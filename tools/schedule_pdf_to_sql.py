#!/usr/bin/env python3
"""
由排課系統匯出的班級課表 PDF 產生可匯入的 SQL。

這些 PDF 是含格線的文字表格（非掃描影像），因此可以完全決定性地解析，
不需要人工轉錄或請 AI 猜測內容。

若一併提供教師課表 PDF，會做雙向交叉驗證：兩份文件由排課系統各自匯出，
內容應互相吻合，任何不一致都代表來源資料有問題，會在報告中列出。

用法：
    python3 tools/schedule_pdf_to_sql.py 班級課表.pdf [-t 教師課表.pdf] [-o out.sql]

本工具需要 pdfplumber。專案內沒有 PHP 以外的執行環境，建議以容器執行：
    docker run --rm -v "$PWD:/work" -w /work pdftools \
        python3 tools/schedule_pdf_to_sql.py ...
"""
import argparse
import collections
import datetime
import re
import sys

import pdfplumber

PERIOD_NUMERALS = "一二三四五六七八九十"
GRADE_NUMERALS = {1: "一", 2: "二", 3: "三", 4: "四", 5: "五", 6: "六", 7: "七", 8: "八", 9: "九"}


def normalize(text: str) -> str:
    """去除 PDF 字距造成的空白，例如「數 學」與「數學」實為同一科目。"""
    return re.sub(r"\s+", "", text or "")


def period_rows(page):
    """逐列取出「第N節」的儲存格，回傳 (節次, 週一~週五的五個儲存格)。"""
    for row in page.extract_table() or []:
        label = normalize(row[1])
        m = re.match(r"第([一二三四五六七八九十]+)節", label)
        if m:
            yield PERIOD_NUMERALS.index(m.group(1)) + 1, row[3:8]


def cell_lines(cell):
    """保留行內空白，共同授課的兩位教師即以空白分隔。"""
    return [x.strip() for x in (cell or "").split("\n") if x.strip()]


class Report:
    """收集解析過程中的異常，最後一次輸出，避免訊息散落。"""

    def __init__(self):
        self.notes = collections.defaultdict(list)

    def add(self, kind, message):
        self.notes[kind].append(message)

    def dump(self, stream=sys.stderr):
        for kind, messages in self.notes.items():
            print(f"\n[{kind}] {len(messages)} 筆", file=stream)
            for m in messages:
                print(f"  {m}", file=stream)


def parse_classes(path, report):
    """解析班級課表，回傳 [{code, grade, no, courses:[{weekday, period, subject, teachers}]}]。"""
    classes = []

    with pdfplumber.open(path) as pdf:
        for page_no, page in enumerate(pdf.pages, 1):
            text = page.extract_text() or ""
            m_code = re.search(r"編號\s*[:：]\s*(\d+)", text)
            m_class = re.search(r"班級\s*[:：]\s*(\d+)\s*年\s*(\d+)\s*班", text)

            if not (m_code and m_class):
                report.add("略過的頁面", f"第 {page_no} 頁不是班級課表")
                continue

            code = m_code.group(1)
            courses = []

            for period, cells in period_rows(page):
                for weekday, cell in enumerate(cells, start=1):
                    lines = cell_lines(cell)
                    if not lines:
                        continue

                    subject, teachers, abbreviated = interpret_cell(code, weekday, period, lines, report)
                    if subject:
                        courses.append({
                            "weekday": weekday,
                            "period": period,
                            "subject": subject,
                            "teachers": teachers,
                            "abbreviated": abbreviated,
                        })

            classes.append({
                "code": code,
                "grade": int(m_class.group(1)),
                "no": int(m_class.group(2)),
                "courses": courses,
            })

    return classes


def interpret_cell(code, weekday, period, lines, report):
    """
    一般儲存格為「科目 / 教師」兩行，另有兩種例外：
      社團\n社團師\n<班級代碼>   末行是重複的班級代碼，捨棄
      社輔\n甲 乙\n/            兩位教師共同授課，末行的斜線為分隔符
    """
    subject = normalize(lines[0])

    if len(lines) == 2:
        return subject, [normalize(lines[1])], False

    where = f"{code} 週{weekday}第{period}節 {subject}"

    if len(lines) == 3 and normalize(lines[2]) == code:
        return subject, [normalize(lines[1])], False

    if len(lines) == 3 and lines[2] == "/":
        # 此格為兩位教師共同授課，姓名以兩字簡稱表示
        teachers = [normalize(t) for t in lines[1].split()]
        report.add("共同授課", f"{where}: {' 與 '.join(teachers)}")
        return subject, teachers, True

    report.add("無法解析的儲存格", f"{where}: {lines}")
    return None, [], False


def expand_abbreviations(classes, report):
    """
    共同授課的儲存格以兩字簡稱表示教師，依全名還原。
    僅處理標記為簡稱的儲存格：「江玲」「陳珊」等亦為兩字，但它們是完整姓名。
    """
    full_names = {t for c in classes for x in c["courses"]
                  for t in x["teachers"] if not x["abbreviated"]}

    for c in classes:
        for course in c["courses"]:
            if not course["abbreviated"]:
                continue

            resolved = []
            for name in course["teachers"]:
                matches = [f for f in full_names if f != name and f.endswith(name)]
                if len(matches) == 1:
                    resolved.append(matches[0])
                else:
                    report.add("簡稱無法對應", f"{c['code']} {course['subject']} 的「{name}」-> {matches or '查無'}")
                    resolved.append(name)
            course["teachers"] = resolved


def parse_teacher_schedules(path):
    """解析教師課表，回傳 {(教師, 週, 節): [(班級代碼, 科目)]}，供交叉驗證使用。"""
    result = collections.defaultdict(list)

    with pdfplumber.open(path) as pdf:
        for page in pdf.pages:
            text = page.extract_text() or ""
            m = re.search(r"教師\s*[:：]\s*(\d+)\s+(\S+)", text)
            if not m:
                continue

            name = m.group(2)

            for period, cells in period_rows(page):
                for weekday, cell in enumerate(cells, start=1):
                    lines = cell_lines(cell)
                    if len(lines) < 2:
                        continue

                    m_class = re.match(r"(\d+)年(\d+)班", normalize(lines[1]))
                    code = f"{m_class.group(1)}{int(m_class.group(2)):02d}" if m_class else lines[1]
                    result[(name, weekday, period)].append((code, normalize(lines[0])))

    return result


def cross_validate(classes, teacher_map, report):
    """比對兩份文件；兩者由同一套排課系統匯出，理應完全吻合。"""
    from_classes = collections.defaultdict(list)
    for c in classes:
        for x in c["courses"]:
            for t in x["teachers"]:
                from_classes[(t, x["weekday"], x["period"])].append((c["code"], x["subject"]))

    confirmed = 0
    for key, entries in teacher_map.items():
        if key not in from_classes:
            report.add("僅見於教師課表", f"{key[0]} 週{key[1]}第{key[2]}節 -> {entries}")
        elif sorted(from_classes[key]) != sorted(entries):
            report.add("兩份文件不一致",
                       f"{key[0]} 週{key[1]}第{key[2]}節 教師表={sorted(entries)} 班級表={sorted(from_classes[key])}")
        else:
            confirmed += 1

    for key, entries in from_classes.items():
        if key not in teacher_map:
            report.add("僅見於班級課表", f"{key[0]} 週{key[1]}第{key[2]}節 -> {entries}（多為無個人課表的外聘教師）")

    return confirmed, len(teacher_map)


def find_teacher_conflicts(classes):
    """
    找出同一教師同時段被排在多個班級的情形。
    資料表的 uk_teacher_timeslot 不允許這種資料，社團課因跨班上課常會觸發。
    """
    slots = collections.defaultdict(list)
    for c in classes:
        for x in c["courses"]:
            slots[(x["teachers"][0], x["weekday"], x["period"])].append((c["code"], x["subject"]))

    return {k: v for k, v in slots.items() if len(v) > 1}


def sql_string(value):
    return "'" + str(value).replace("\\", "\\\\").replace("'", "''") + "'"


def build_sql(classes, conflicts, source_files):
    classes = sorted(classes, key=lambda c: c["code"])

    teachers = sorted({t for c in classes for x in c["courses"] for t in x["teachers"]})
    subjects = sorted({x["subject"] for c in classes for x in c["courses"]})
    max_period = max(x["period"] for c in classes for x in c["courses"])
    max_weekday = max(x["weekday"] for c in classes for x in c["courses"])

    class_id = {c["code"]: i for i, c in enumerate(classes, 1)}
    teacher_id = {t: i for i, t in enumerate(teachers, 1)}
    subject_id = {s: i for i, s in enumerate(subjects, 1)}
    slot_id = {(w, p): (w - 1) * max_period + p
               for w in range(1, max_weekday + 1) for p in range(1, max_period + 1)}

    out = []
    w = out.append

    w("-- " + "=" * 60)
    w("-- 課表資料")
    w("-- " + "=" * 60)
    w("--")
    w("-- 本檔由 tools/schedule_pdf_to_sql.py 自動產生，請勿手動編輯；")
    w("-- 課表更新時重新執行該工具即可。")
    w("--")
    for f in source_files:
        w(f"-- 來源: {f}")
    w(f"-- 產生時間: {datetime.datetime.now():%Y-%m-%d %H:%M:%S}")
    w(f"-- 內容: {len(classes)} 班、{len(teachers)} 位教師、{len(subjects)} 種科目、"
      f"{sum(len(c['courses']) for c in classes)} 堂課")
    w("--")
    w("-- 匯入方式（feedback 資料表不受影響）:")
    w("--   docker exec -i hn-db mariadb -u root -p<密碼> <資料庫> < 本檔")
    w("-- " + "=" * 60)
    w("")
    w("SET NAMES utf8mb4;")
    w("")
    w("START TRANSACTION;")
    w("")
    w("-- 依外鍵順序清除舊資料；使用 DELETE 而非 TRUNCATE 以保持可回溯")
    for table in ("schedule", "class", "teacher", "subject", "timeslot"):
        w(f"DELETE FROM `{table}`;")
    w("")

    w("-- 班級")
    values = [f"({class_id[c['code']]}, {sql_string(c['code'])}, "
              f"{sql_string(GRADE_NUMERALS[c['grade']] + '年' + GRADE_NUMERALS[c['no']] + '班')})"
              for c in classes]
    w("INSERT INTO `class` (`class_id`, `class_code`, `class_name`) VALUES")
    w(",\n".join("  " + v for v in values) + ";")
    w("")

    w("-- 教師")
    w("INSERT INTO `teacher` (`teacher_id`, `teacher_name`) VALUES")
    w(",\n".join(f"  ({teacher_id[t]}, {sql_string(t)})" for t in teachers) + ";")
    w("")

    w("-- 科目")
    w("INSERT INTO `subject` (`subject_id`, `subject_name`) VALUES")
    w(",\n".join(f"  ({subject_id[s]}, {sql_string(s)})" for s in subjects) + ";")
    w("")

    w(f"-- 時段：{max_weekday} 天 x 每天 {max_period} 節")
    w("INSERT INTO `timeslot` (`timeslot_id`, `weekday`, `period`) VALUES")
    w(",\n".join(f"  ({slot_id[(wd, p)]}, {wd}, {p})"
                 for wd in range(1, max_weekday + 1)
                 for p in range(1, max_period + 1)) + ";")
    w("")

    w("-- 課表")
    w("INSERT INTO `schedule` (`class_id`, `subject_id`, `teacher_id`, `timeslot_id`) VALUES")

    rows, skipped = [], []
    seen = set()

    for c in classes:
        for x in sorted(c["courses"], key=lambda x: (x["weekday"], x["period"])):
            teacher = x["teachers"][0]
            key = (teacher, x["weekday"], x["period"])

            line = (f"  ({class_id[c['code']]}, {subject_id[x['subject']]}, "
                    f"{teacher_id[teacher]}, {slot_id[(x['weekday'], x['period'])]})")
            note = f"{c['code']} 週{x['weekday']}第{x['period']}節 {x['subject']}／{teacher}"

            if key in seen:
                # uk_teacher_timeslot 會拒絕這筆，註解保留以便人工判斷
                skipped.append((line, note))
                continue

            seen.add(key)
            rows.append((line, note + ("　← 共同授課，另有 " + "、".join(x["teachers"][1:])
                                       if len(x["teachers"]) > 1 else "")))

    # 逗號必須排在註解之前，否則會被 -- 一併註解掉，導致 VALUES 之間缺少分隔
    w("\n".join(
        f"{line}{',' if i < len(rows) - 1 else ';'}  -- {note}"
        for i, (line, note) in enumerate(rows)
    ))
    w("")

    if skipped:
        w("-- " + "-" * 58)
        w(f"-- 以下 {len(skipped)} 筆因教師於同一時段已被排課而未匯入。")
        w("-- 資料表的 uk_teacher_timeslot 不允許同一教師同時段教兩個班；")
        w("-- 社團課跨班上課時會出現此情形。確認後可自行取消註解並調整。")
        w("-- " + "-" * 58)
        for line, note in skipped:
            w(f"-- {line.strip()}  -- {note}")
        w("")

    w("COMMIT;")
    w("")

    return "\n".join(out), len(rows), len(skipped)


def main():
    ap = argparse.ArgumentParser(description="由班級課表 PDF 產生可匯入的 SQL")
    ap.add_argument("class_pdf", help="班級課表 PDF")
    ap.add_argument("-t", "--teacher-pdf", help="教師課表 PDF，提供時會進行交叉驗證")
    ap.add_argument("-o", "--output", help="輸出的 SQL 檔，預設輸出至標準輸出")
    args = ap.parse_args()

    report = Report()

    classes = parse_classes(args.class_pdf, report)
    expand_abbreviations(classes, report)

    if not classes:
        print("錯誤: 未解析到任何班級課表", file=sys.stderr)
        return 1

    print(f"解析到 {len(classes)} 個班級、"
          f"{sum(len(c['courses']) for c in classes)} 堂課", file=sys.stderr)

    sources = [args.class_pdf]

    if args.teacher_pdf:
        sources.append(args.teacher_pdf)
        confirmed, total = cross_validate(classes, parse_teacher_schedules(args.teacher_pdf), report)
        print(f"交叉驗證: 教師課表 {total} 筆中有 {confirmed} 筆與班級課表吻合", file=sys.stderr)

    conflicts = find_teacher_conflicts(classes)
    sql, inserted, skipped = build_sql(classes, conflicts, sources)

    if args.output:
        with open(args.output, "w", encoding="utf-8") as f:
            f.write(sql)
        print(f"已寫出 {args.output}：{inserted} 筆課程"
              + (f"，另有 {skipped} 筆以註解保留" if skipped else ""), file=sys.stderr)
    else:
        print(sql)

    report.dump()
    return 0


if __name__ == "__main__":
    sys.exit(main())

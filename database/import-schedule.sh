#!/usr/bin/env bash
#
# 將課表資料匯入指定學校的資料庫。
#
#   用法：./database/import-schedule.sh <資料庫> [-y]
#   範例：./database/import-schedule.sh yjm_schedule
#
# 匯入來源固定為 database/schedules/<資料庫>.sql，該檔由
# tools/schedule_pdf_to_sql.py 產生並納入版本控制，因此伺服器端
# 只需 git pull 後執行本腳本，不必另外傳檔。
#
# 資料庫或資料表不存在時會自動建立。意見回饋（feedback）不受影響。
#
set -euo pipefail

DB=""
ASSUME_YES=0

for arg in "$@"; do
    case "$arg" in
        -y|--yes) ASSUME_YES=1 ;;
        -h|--help) sed -n '2,12p' "$0" | sed 's/^# \?//'; exit 0 ;;
        -*) echo "錯誤: 未知選項 $arg" >&2; exit 1 ;;
        *)  DB="$arg" ;;
    esac
done

if [ -z "$DB" ]; then
    echo "用法: $0 <資料庫> [-y]" >&2
    echo >&2
    echo "可用的課表檔:" >&2
    for f in "$(dirname "$0")"/schedules/*.sql; do
        [ -e "$f" ] && echo "  $(basename "${f%.sql}")" >&2
    done
    exit 1
fi

if ! [[ "$DB" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "錯誤: 資料庫名稱只能包含英文、數字與底線" >&2
    exit 1
fi

cd "$(dirname "$0")/.."

SQL_FILE="database/schedules/${DB}.sql"

if [ ! -f "$SQL_FILE" ]; then
    echo "錯誤: 找不到 $SQL_FILE" >&2
    exit 1
fi

if [ ! -f .env ]; then
    echo "錯誤: 找不到 .env，請先由 .env.example 複製" >&2
    exit 1
fi

# 逐行取值而非 source，因為密碼可能含有 shell 特殊字元
DB_ROOT_PASS="$(grep -E '^DB_ROOT_PASS=' .env | head -1 | cut -d= -f2-)"

if [ -z "$DB_ROOT_PASS" ]; then
    echo "錯誤: .env 缺少 DB_ROOT_PASS" >&2
    exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -qx schedule-db; then
    echo "錯誤: schedule-db 容器未執行，請先 docker compose up -d" >&2
    exit 1
fi

# 查詢用：不加 -i，否則 docker exec 會吃掉 stdin，
# 連帶影響呼叫端後續的互動輸入。
query_sql() {
    docker exec schedule-db mariadb -u root -p"$DB_ROOT_PASS" "$@" </dev/null
}

# 匯入用：需要從 stdin 讀入 SQL
run_sql() {
    docker exec -i schedule-db mariadb -u root -p"$DB_ROOT_PASS" "$@"
}

# 資料庫與資料表不存在時先建立；add-school.sh 可重複執行
./database/add-school.sh "$DB" >/dev/null </dev/null

echo "資料庫 : ${DB}"
echo "來源   : ${SQL_FILE}"
grep -m1 '^-- 內容:' "$SQL_FILE" | sed 's/^-- /匯入   : /' || true

CURRENT="$(query_sql -N -B -e "SELECT COUNT(*) FROM schedule;" "$DB" 2>/dev/null || echo 0)"

if [ "$CURRENT" -gt 0 ]; then
    echo "覆蓋   : 現有 ${CURRENT} 筆課程將被取代（意見回饋不受影響）"
fi

if [ "$ASSUME_YES" -eq 0 ]; then
    printf '繼續？[y/N] '
    read -r answer
    case "$answer" in
        [Yy]*) ;;
        *) echo "已取消。"; exit 0 ;;
    esac
fi

run_sql "$DB" < "$SQL_FILE"

echo
query_sql -t -e "SELECT
    (SELECT COUNT(*) FROM class)    AS 班級,
    (SELECT COUNT(*) FROM teacher)  AS 教師,
    (SELECT COUNT(*) FROM subject)  AS 科目,
    (SELECT COUNT(*) FROM timeslot) AS 時段,
    (SELECT COUNT(*) FROM schedule) AS 課程;" "$DB"
echo "匯入完成。"

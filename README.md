# 調課查詢系統

提供班級課表查詢，並在選取課堂後自動標示出可以調課的時段。

單一部署可同時服務多間學校：系統依照請求的網域決定目前是哪一間學校，並連往
該校專屬的資料庫，各校資料在資料庫層完全分離。校名、班級結構與作息節數都來自
該校自己的資料，不寫死在程式裡。

## 功能

- **班級課表查詢** — 選擇年級與班級後顯示該班完整課表
- **教師課表即時預覽** — 滑鼠移至任一課堂，右側同步顯示該教師的完整課表
- **調課衝突檢測** — 點選一堂課後，自動將無法對調的時段標記為不可選，判斷依據為：
  - 原授課教師在該時段已有其他課程
  - 目標時段的授課教師在原時段已有其他課程
- **調課限制選項** — 可另行排除最後一節、社團課與班級活動，或限制國文/數學只能與國文/數學對調
- **意見回饋** — 建議與錯誤回報表單

## 技術架構

| 項目 | 內容 |
| --- | --- |
| 後端 | PHP 8.5 (FPM)，以 PDO 連線資料庫 |
| 資料庫 | MariaDB 10.11，每校一個 database |
| 前端 | 原生 JavaScript 與 CSS，無建置流程 |
| 對外連線 | Cloudflare Tunnel |

課表資料由 [showSchedule.php](showSchedule.php) 一次查詢後隨頁面輸出，所有衝突判斷皆在瀏覽器端由 [js/main.js](js/main.js) 完成，操作過程不再發送請求。

### 容器組成

本專案為自帶資料庫與對外通道的完整堆疊，不依賴任何外部容器或共用網路。
不論服務幾間學校，容器數量都是固定的四個。

```
             ┌──────── frontend ────────┐   ┌─ backend (internal) ─┐
Internet ─▶ schedule-tunnel ─▶ schedule-web ─▶ schedule-php ───────────────────▶ schedule-db
            cloudflared   nginx    php-fpm                     mariadb
```

| 容器 | 用途 | 對外 |
| --- | --- | --- |
| `schedule-tunnel` | Cloudflare Tunnel，唯一的對外出入口 | 主動外連 |
| `schedule-web` | Nginx，靜態檔與 PHP 路由 | 否 |
| `schedule-php` | PHP-FPM | 否 |
| `schedule-db` | MariaDB，資料持久化於 `db-data` volume | 否 |

主機不需開放任何 port。`backend` 網路設為 `internal`，資料庫既連不上網際網路，也無法被 `schedule-tunnel` 存取。

### 多校運作方式

[includes/schools.php](includes/schools.php) 是學校註冊表，將網域對應到該校的資料庫與設定：

```
schedule-a.example.tw  ─▶  sch_a   （A 國中，每天 8 節）
schedule-b.example.tw  ─▶  sch_b   （B 國中，每天 7 節）
```

未註冊的網域一律回應 404，不會退回任何預設學校 —— 避免把某校的資料顯示在非
該校的網域上。

校名來自註冊表；班級清單、年級、每日節數與上課天數則由該校資料庫的 `class`
與 `timeslot` 資料表推得，因此各校班級數或作息不同時無須改動程式碼。社團課與
班級活動的時段屬校本作息，記在註冊表的 `activity_slots`。

## 部署

```bash
# 1. 建立設定檔並填入密碼與 tunnel token
cp .env.example .env

# 2. 啟動
docker compose up -d --build

# 3. 為每間學校建立資料庫（詳見下節）
./database/add-school.sh sch_hunei
```

`.env` 的所有變數皆為必填，缺少任何一項 `docker compose` 會直接中止並指出缺少的項目。

```
DB_USER=schedule_user
DB_PASS=<應用程式使用的密碼>
DB_ROOT_PASS=<維護用的 root 密碼，與上者不同>
TUNNEL_TOKEN=<Cloudflare Dashboard 取得的 tunnel token>
```

Tunnel 於 Cloudflare Dashboard 的 **Zero Trust → Networks → Tunnels** 建立。
每間學校在同一條 tunnel 底下各新增一個 public hostname，service 一律指向
`http://schedule-web:80`；路由設定全部留在 Dashboard 上，專案內不需維護設定檔。

## 新增一間學校

學校註冊表 [includes/schools.php](includes/schools.php) 納入版本控制，
**設定改在 repo、不要在伺服器上直接編輯**，否則之後 `git pull` 會衝突。

```php
// 1. 於 includes/schools.php 加入一筆並提交
'newschool' => [
    'hosts' => ['schedule-new.example.tw', 'newschool.localhost'],
    'name' => '新設國中',
    'database' => 'new_schedule',
    'activity_slots' => [[2, 6], [2, 7]],   // 週二第 6、7 節，無則留空陣列
],
```

```bash
# 2. 於伺服器取得設定並建立資料庫
cd /opt/schedule-system && git pull
./database/add-school.sh new_schedule
```

3. 於 Cloudflare Tunnel 新增該網域，service 指向 `http://schedule-web:80`

接著依下節匯入該校的課表資料即可。`class`、`teacher`、`subject`、
`timeslot`、`schedule` 五張表的內容決定了該校的班級、年級與作息，
系統會自動反映。

`hosts` 可填多個網域，所以正式網域與本機開發用的網域能並存於同一筆設定，
不需要為了開發另外準備一份檔案。

### 本機覆寫

若需要臨時調整而不想動到版本控制中的檔案，可建立
`includes/schools.local.php`，其內容會依學校代號覆寫或新增註冊表：

```php
<?php
return [
    'yjm' => [
        'hosts' => ['demo.localhost'],
        'name' => '測試用',
        'database' => 'yjm_schedule',
        'activity_slots' => [],
    ],
];
```

該檔不納入版本控制，因此不會與 `git pull` 衝突。未列出的學校維持原設定。

## 更新課表

排課系統匯出的班級課表 PDF 是含格線的文字表格，可直接解析，不需人工轉錄。
一併提供教師課表時會做雙向交叉驗證：兩份文件由排課系統各自匯出，內容應互相
吻合，任何不一致都會列出。

**1. 在本機產生 SQL**（需要 pdfplumber，以容器執行最省事）

```bash
docker build -t pdftools tools/

docker run --rm -v "$PWD:/work" -w /work pdftools \
    python3 tools/schedule_pdf_to_sql.py 班級課表.pdf \
        -t 教師課表.pdf \
        -o database/schedules/<資料庫>.sql
```

檔名必須與該校的資料庫同名，匯入腳本依此尋找。產生的 SQL 不納入版控。

**2. 送到伺服器**

```bash
scp database/schedules/<資料庫>.sql \
    deploy@<伺服器>:/opt/schedule-system/database/schedules/
```

**3. 在伺服器上匯入**

```bash
cd /opt/schedule-system
./database/import-schedule.sh <資料庫>
```

腳本會自動建立不存在的資料庫與資料表、顯示即將匯入的內容與將被覆蓋的筆數、
待確認後才寫入，最後印出各資料表的筆數。加上 `-y` 可略過確認。

意見回饋（`feedback`）不受影響，只有課表相關的五張表會被取代。

## 資料庫維護

資料庫不對外開放 port，一律透過 `docker exec` 操作。注意指令最後要指定學校的
資料庫名稱。

```bash
# 進入某校的 SQL shell
docker exec -it schedule-db mariadb -u root -p sch_hunei

# 備份單一學校
docker exec schedule-db mariadb-dump -u root -p"$DB_ROOT_PASS" \
    --single-transaction sch_hunei > hunei-$(date +%F).sql

# 備份全部學校
docker exec schedule-db mariadb-dump -u root -p"$DB_ROOT_PASS" \
    --single-transaction --all-databases > all-$(date +%F).sql

# 還原
docker exec -i schedule-db mariadb -u root -p"$DB_ROOT_PASS" sch_hunei < backup.sql
```

## 日誌

各容器的日誌皆輸出至標準輸出，以 `docker logs` 檢視。課表查詢記錄會標示學校：

```bash
docker logs -f schedule-php      # PHP 錯誤與課表查詢記錄
docker logs -f schedule-web      # Nginx 存取日誌
docker logs -f schedule-db       # 資料庫
docker logs -f schedule-tunnel   # Tunnel 連線狀態

# 只看某一校
docker logs schedule-php 2>&1 | grep "School: hunei"
```

## 專案結構

```
├── index.php                  首頁：年級與班級選擇
├── showSchedule.php           課表顯示與調課檢測
├── feedback.php               意見回饋表單
├── information.php            網站資訊與更新日誌
├── includes/
│   ├── schools.php            學校註冊表：網域 → 資料庫與校本設定
│   ├── schools.local.php      本機覆寫（選用，未納入版控）
│   ├── config.php             依網域選定學校並建立資料庫連線
│   └── error_page.php         共用的錯誤頁面
├── css/ js/                   前端樣式與腳本
├── database/
│   ├── schema.sql             資料表結構（每校各套用一份）
│   ├── sample_data.sql        範例資料
│   ├── add-school.sh          建立新學校的資料庫
│   ├── import-schedule.sh     匯入課表資料
│   └── schedules/             各校課表 SQL（產生物，未納入版控）
├── tools/
│   └── schedule_pdf_to_sql.py 由課表 PDF 產生 SQL
└── nginx/default.conf         Nginx 路由設定
```

靜態資源的網址皆帶有 `?v=` 版本參數，數值取自檔案修改時間（[includes/config.php](includes/config.php)），修改 CSS 或 JS 後瀏覽器會自動取得新版本，無須手動清除快取。

## 授權

MIT License © 2025 Steven Chin

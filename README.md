# 調課查詢系統

提供班級課表查詢，並在選取課堂後自動標示出可以調課的時段。

顯示用的校名由 `SCHOOL_NAME` 環境變數指定，佈署至不同學校時無須修改程式碼。

## 功能

- **班級課表查詢** — 選擇年級與班級後顯示該班完整課表
- **教師課表即時預覽** — 滑鼠移至任一課堂，右側同步顯示該教師的完整課表
- **調課衝突檢測** — 點選一堂課後，自動將無法對調的時段標記為不可選，判斷依據為：
  - 原授課教師在該時段已有其他課程
  - 目標時段的授課教師在原時段已有其他課程
- **調課限制選項** — 可另行排除第八節、社團課與班級活動，或限制國文/數學只能與國文/數學對調
- **意見回饋** — 建議與錯誤回報表單

## 技術架構

| 項目 | 內容 |
| --- | --- |
| 後端 | PHP 8.5 (FPM)，以 PDO 連線資料庫 |
| 資料庫 | MariaDB 10.11 |
| 前端 | 原生 JavaScript 與 CSS，無建置流程 |
| 對外連線 | Cloudflare Tunnel |

課表資料由 [showSchedule.php](showSchedule.php) 一次查詢後隨頁面輸出，所有衝突判斷皆在瀏覽器端由 [js/main.js](js/main.js) 完成，操作過程不再發送請求。

### 容器組成

本專案為自帶資料庫與對外通道的完整堆疊，不依賴任何外部容器或共用網路。

```
                 ┌─────────── frontend ───────────┐   ┌─ backend (internal) ─┐
Internet ──▶ hn-tunnel ──▶ hn-web ──▶ hn-php ─────────────────▶ hn-db
             cloudflared    nginx      php-fpm                   mariadb
```

| 容器 | 用途 | 對外 |
| --- | --- | --- |
| `hn-tunnel` | Cloudflare Tunnel，唯一的對外出入口 | 主動外連 |
| `hn-web` | Nginx，靜態檔與 PHP 路由 | 否 |
| `hn-php` | PHP-FPM | 否 |
| `hn-db` | MariaDB，資料持久化於 `db-data` volume | 否 |

主機不需開放任何 port。`backend` 網路設為 `internal`，資料庫既連不上網際網路，也無法被 `hn-tunnel` 存取。

## 部署

```bash
# 1. 建立設定檔並填入密碼與 tunnel token
cp .env.example .env

# 2. 啟動（首次啟動會自動以 database/schema.sql 建立資料表）
docker compose up -d --build
```

`.env` 的所有變數皆為必填，缺少任何一項 `docker compose` 會直接中止並指出缺少的項目。

```
SCHOOL_NAME=<顯示於標題與頁首的校名，未設定時為「國中」>
DB_NAME=hn_schedule
DB_USER=schedule_user
DB_PASS=<應用程式使用的密碼>
DB_ROOT_PASS=<維護用的 root 密碼，與上者不同>
TUNNEL_TOKEN=<Cloudflare Dashboard 取得的 tunnel token>
```

除 `SCHOOL_NAME` 外皆為必填 —— 校名未設定時會套用通用預設值，不會阻擋啟動。

Tunnel 於 Cloudflare Dashboard 的 **Zero Trust → Networks → Tunnels** 建立，新增 public hostname 時將 service 指向 `http://hn-web:80`；路由設定全部留在 Dashboard 上，專案內不需維護設定檔。

## 資料庫維護

資料庫不對外開放 port，一律透過 `docker exec` 操作。

```bash
# 進入 SQL shell
docker exec -it hn-db mariadb -u root -p hn_schedule

# 備份（輸出含 DROP TABLE，可直接覆蓋還原）
docker exec hn-db mariadb-dump -u root -p"$DB_ROOT_PASS" \
    --single-transaction hn_schedule > backup-$(date +%F).sql

# 還原
docker exec -i hn-db mariadb -u root -p"$DB_ROOT_PASS" hn_schedule < backup.sql
```

### 由舊的共用資料庫搬遷

在舊主機匯出後，於新環境還原即可。請使用 `mariadb-dump` 而非 phpMyAdmin 匯出 —
前者預設包含 `DROP TABLE IF EXISTS`，可直接覆蓋掉首次啟動時自動建立的空資料表。

```bash
# 舊環境
docker exec <舊容器> mariadb-dump -u root -p --single-transaction hn_schedule > migrate.sql

# 新環境
docker exec -i hn-db mariadb -u root -p"$DB_ROOT_PASS" hn_schedule < migrate.sql
```

## 日誌

各容器的日誌皆輸出至標準輸出，以 `docker logs` 檢視：

```bash
docker logs -f hn-php      # PHP 錯誤與課表查詢記錄
docker logs -f hn-web      # Nginx 存取日誌
docker logs -f hn-db       # 資料庫
docker logs -f hn-tunnel   # Tunnel 連線狀態
```

## 專案結構

```
├── index.php              首頁：年級與班級選擇
├── showSchedule.php       課表顯示與調課檢測
├── feedback.php           意見回饋表單
├── information.php        網站資訊與更新日誌
├── includes/config.php    資料庫連線與靜態資源版本號
├── css/ js/               前端樣式與腳本
├── database/              資料庫結構與範例資料
└── nginx/default.conf     Nginx 路由設定
```

靜態資源的網址皆帶有 `?v=` 版本參數，數值取自檔案修改時間（[includes/config.php](includes/config.php)），修改 CSS 或 JS 後瀏覽器會自動取得新版本，無須手動清除快取。

## 授權

MIT License © 2025 Steven Chin

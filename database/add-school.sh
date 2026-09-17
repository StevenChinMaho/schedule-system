#!/usr/bin/env bash
#
# 為一間新學校建立專屬資料庫並套用資料表結構。
#
#   用法：./database/add-school.sh <database>
#   範例：./database/add-school.sh sch_hunei
#
# 執行後仍需在 includes/schools.php 加入該校設定，並於 Cloudflare Tunnel
# 新增對應網域。
#
set -euo pipefail

DB="${1:-}"

if [ -z "$DB" ]; then
    echo "用法: $0 <database>" >&2
    exit 1
fi

# 資料庫名稱會直接組進 SQL，先限制字元避免注入
if ! [[ "$DB" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "錯誤: 資料庫名稱只能包含英文、數字與底線" >&2
    exit 1
fi

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
    echo "錯誤: 找不到 .env，請先由 .env.example 複製" >&2
    exit 1
fi

# 逐行取值而非 source，因為密碼可能含有 shell 特殊字元
env_get() {
    grep -E "^$1=" .env | head -1 | cut -d= -f2-
}

DB_ROOT_PASS="$(env_get DB_ROOT_PASS)"
DB_USER="$(env_get DB_USER)"

if [ -z "$DB_ROOT_PASS" ] || [ -z "$DB_USER" ]; then
    echo "錯誤: .env 缺少 DB_ROOT_PASS 或 DB_USER" >&2
    exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -qx hn-db; then
    echo "錯誤: hn-db 容器未執行，請先 docker compose up -d" >&2
    exit 1
fi

run_sql() {
    docker exec -i hn-db mariadb -u root -p"$DB_ROOT_PASS" "$@"
}

echo "建立資料庫 ${DB} 並授權給 ${DB_USER}..."
run_sql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB}\`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
GRANT ALL PRIVILEGES ON \`${DB}\`.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
SQL

# schema.sql 的 CREATE TABLE 不含 IF NOT EXISTS，重複套用會失敗，
# 因此已有資料表時就跳過，讓這支腳本可以安全重跑
TABLE_COUNT="$(run_sql -N -B -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${DB}';")"

if [ "$TABLE_COUNT" -gt 0 ]; then
    echo "資料庫已有 ${TABLE_COUNT} 張資料表，略過結構匯入。"
else
    echo "匯入資料表結構..."
    run_sql "$DB" < database/schema.sql
    echo "完成。"
fi

echo
echo "接下來："
echo "  1. 於 includes/schools.php 加入此校設定（database 填 ${DB}）"
echo "  2. 於 Cloudflare Tunnel 新增該校網域，service 指向 http://hn-web:80"

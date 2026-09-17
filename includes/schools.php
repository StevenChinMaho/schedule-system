<?php
/**
 * 學校註冊表。
 *
 * 系統依照請求的網域決定目前是哪一間學校，並連往該校專屬的資料庫，
 * 各校資料在資料庫層完全分離。
 *
 * 新增一間學校的步驟：
 *   1. 在下方加入一筆設定並提交
 *   2. 於伺服器 git pull，執行 database/add-school.sh <database>
 *   3. 於 Cloudflare Tunnel 新增該網域，service 指向 http://schedule-web:80
 *
 * 此檔不含任何密碼，納入版本控制，伺服器端不應直接修改 ——
 * 需要本機測試網域或臨時覆寫時，請改用下方的 schools.local.php。
 *
 * 欄位說明：
 *   hosts     該校使用的網域，可填多個（正式網域與本機開發網域可並存）
 *   name      顯示於頁面標題與頁首的校名
 *   database  該校專屬的資料庫名稱
 *   activity_slots
 *             「社團課與班級活動」對應的時段，格式為 [星期, 節次]。
 *             此為各校校本作息，沒有的話留空陣列即可。
 *
 * 課表的天數與每日節數不在此設定，系統會直接從該校 timeslot 資料表推得。
 */
$schools = [
    'hn' => [
        'hosts' => ['hn-schedule.kururinpa.dev', 'hn.localhost'],
        'name' => '湖內國中',
        'database' => 'hn_schedule',
        'activity_slots' => [[2, 6], [2, 7]],
    ],
    'yjm' => [
        'hosts' => ['yjm-schedule.kururinpa.dev', 'yjm.localhost'],
        'name' => '一甲國中',
        'database' => 'yjm_schedule',
        // 班會／自主為週五第六節、社團為週五第七節
        'activity_slots' => [[5, 6], [5, 7]],
    ],
];

/*
 * 本機覆寫：存在 schools.local.php 時，其內容會依學校代號覆寫或新增上表。
 * 供開發與臨時測試使用，不納入版本控制，因此不會與 git pull 衝突。
 */
$local = __DIR__ . '/schools.local.php';

if ( file_exists($local) )
{
    $schools = array_replace( $schools, require $local );
}

return $schools;

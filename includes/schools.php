<?php
/**
 * 學校註冊表。
 *
 * 系統依照請求的網域決定目前是哪一間學校，並連往該校專屬的資料庫，
 * 各校資料在資料庫層完全分離。
 *
 * 新增一間學校的步驟：
 *   1. 在下方加入一筆設定
 *   2. 執行 database/add-school.sh <database> 建立資料庫與資料表
 *   3. 於 Cloudflare Tunnel 新增該網域，service 指向 http://hn-web:80
 *
 * 此檔不含任何密碼，可安全納入版本控制。
 *
 * 欄位說明：
 *   hosts     該校使用的網域，可填多個（例如正式網域與測試網域）
 *   name      顯示於頁面標題與頁首的校名
 *   database  該校專屬的資料庫名稱
 *   activity_slots
 *             「社團課與班級活動」對應的時段，格式為 [星期, 節次]。
 *             此為各校校本作息，沒有的話留空陣列即可。
 *
 * 課表的天數與每日節數不在此設定，系統會直接從該校 timeslot 資料表推得。
 */
return [
    'hunei' => [
        'hosts' => ['schedule.example.tw'],
        'name' => '湖內國中',
        'database' => 'sch_hunei',
        'activity_slots' => [[2, 6], [2, 7]],
    ],
];

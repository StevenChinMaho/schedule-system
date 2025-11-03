-- ============================================
-- 湖內國中調課系統 - 資料庫結構
-- ============================================
-- 
-- 資料表說明：
-- - class: 班級資料（12個班級）
-- - teacher: 教師資料
-- - subject: 科目資料
-- - timeslot: 時段資料（週一~五，每天8節，共40個時段）
-- - schedule: 課表資料（連結班級、教師、科目、時段）
-- - feedback: 意見回饋資料
--
-- 重要約束：
-- - 每個時段一個班級只能有一門課
-- - 每個時段一位教師只能教一門課
-- 
-- 索引設計：
-- - uk_class_timeslot: 防止同班同時段重複排課
-- - uk_teacher_timeslot: 防止同師同時段重複排課
-- - idx_class_lookup: 查詢班級相關教師用（showSchedule.php）
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- 資料表結構 `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_code` char(3) NOT NULL COMMENT '班級代碼 (如: 701)',
  `class_name` varchar(50) NOT NULL COMMENT '班級名稱 (如: 七年一班)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='班級資料表';

-- --------------------------------------------------------

--
-- 資料表結構 `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `teacher_name` varchar(50) NOT NULL COMMENT '教師姓名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='教師資料表';

-- --------------------------------------------------------

--
-- 資料表結構 `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(50) NOT NULL COMMENT '科目名稱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='科目資料表';

-- --------------------------------------------------------

--
-- 資料表結構 `timeslot`
--

CREATE TABLE `timeslot` (
  `timeslot_id` int(11) NOT NULL,
  `weekday` tinyint(4) NOT NULL COMMENT '星期幾 (1=週一, 5=週五)',
  `period` tinyint(4) NOT NULL COMMENT '第幾節 (1-8)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='時段資料表';

-- --------------------------------------------------------

--
-- 資料表結構 `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `timeslot_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='課表資料表';

-- --------------------------------------------------------

--
-- 資料表結構 `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `feedback_type` enum('建議','錯誤回報','其他') NOT NULL DEFAULT '其他' COMMENT '回饋類型',
  `feedback_content` text NOT NULL COMMENT '回饋內容',
  `contact_info` varchar(100) DEFAULT NULL COMMENT '聯絡方式（選填）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '提交時間',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP位址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='意見回饋資料表';

-- --------------------------------------------------------

--
-- 索引與主鍵
--

--
-- 資料表索引 `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `uk_class_code` (`class_code`);

--
-- 資料表索引 `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`);

--
-- 資料表索引 `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`);

--
-- 資料表索引 `timeslot`
--
ALTER TABLE `timeslot`
  ADD PRIMARY KEY (`timeslot_id`),
  ADD UNIQUE KEY `uk_weekday_period` (`weekday`, `period`);

--
-- 資料表索引 `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `uk_class_timeslot` (`class_id`, `timeslot_id`) COMMENT '確保同一班級時段不重複',
  ADD UNIQUE KEY `uk_teacher_timeslot` (`teacher_id`, `timeslot_id`) COMMENT '確保同一教師時段不重複',
  ADD KEY `idx_class_lookup` (`class_id`, `teacher_id`) COMMENT '查詢班級相關教師用',
  ADD KEY `fk_schedule_subject` (`subject_id`),
  ADD KEY `fk_schedule_timeslot` (`timeslot_id`);

--
-- 資料表索引 `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idx_created_at` (`created_at`);

-- --------------------------------------------------------

--
-- 自動遞增設定
--

ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `timeslot`
  MODIFY `timeslot_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 外鍵約束
--

ALTER TABLE `schedule`
  ADD CONSTRAINT `fk_schedule_class` 
    FOREIGN KEY (`class_id`) 
    REFERENCES `class` (`class_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_subject` 
    FOREIGN KEY (`subject_id`) 
    REFERENCES `subject` (`subject_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_teacher` 
    FOREIGN KEY (`teacher_id`) 
    REFERENCES `teacher` (`teacher_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_timeslot` 
    FOREIGN KEY (`timeslot_id`) 
    REFERENCES `timeslot` (`timeslot_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
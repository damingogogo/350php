-- 学联界高校教学资源共享平台 假数据填充脚本

USE `edu_resource`;

-- 插入管理员
INSERT INTO `users` (`username`, `nickname`, `email`, `password`, `role`, `avatar`, `school`, `bio`, `created_at`, `updated_at`) VALUES
('admin', '管理员', 'admin@example.com', '123456', 'admin', 'https://picsum.photos/seed/admin/200/200', NULL, '系统管理员', NOW(), NOW());

-- 插入教师用户
INSERT INTO `users` (`username`, `nickname`, `email`, `password`, `role`, `avatar`, `school`, `bio`, `created_at`, `updated_at`) VALUES
('teacher1', '张老师', 'teacher1@example.com', '123456', 'teacher', 'https://picsum.photos/seed/teacher1/200/200', '北京大学', '资深计算机科学教师', NOW(), NOW()),
('teacher2', '李老师', 'teacher2@example.com', '123456', 'teacher', 'https://picsum.photos/seed/teacher2/200/200', '清华大学', '数学系教授', NOW(), NOW()),
('teacher3', '王老师', 'teacher3@example.com', '123456', 'teacher', 'https://picsum.photos/seed/teacher3/200/200', '复旦大学', '物理学院副教授', NOW(), NOW());

-- 插入学生用户
INSERT INTO `users` (`username`, `nickname`, `email`, `password`, `role`, `avatar`, `school`, `created_at`, `updated_at`) VALUES
('student1', '小明', 'student1@example.com', '123456', 'student', 'https://picsum.photos/seed/student1/200/200', '北京大学', NOW(), NOW()),
('student2', '小红', 'student2@example.com', '123456', 'student', 'https://picsum.photos/seed/student2/200/200', '清华大学', NOW(), NOW()),
('student3', '小华', 'student3@example.com', '123456', 'student', 'https://picsum.photos/seed/student3/200/200', '复旦大学', NOW(), NOW()),
('student4', '小李', 'student4@example.com', '123456', 'student', 'https://picsum.photos/seed/student4/200/200', '浙江大学', NOW(), NOW()),
('student5', '小张', 'student5@example.com', '123456', 'student', 'https://picsum.photos/seed/student5/200/200', '上海交通大学', NOW(), NOW());

-- 插入游客用户
INSERT INTO `users` (`username`, `nickname`, `email`, `password`, `role`, `avatar`, `created_at`, `updated_at`) VALUES
('guest1', '游客1', 'guest1@example.com', '123456', 'guest', 'https://picsum.photos/seed/guest1/200/200', NOW(), NOW()),
('guest2', '游客2', 'guest2@example.com', '123456', 'guest', 'https://picsum.photos/seed/guest2/200/200', NOW(), NOW());

-- 插入分类
INSERT INTO `categories` (`name`, `icon`, `image`, `description`, `parent_id`, `sort`, `created_at`, `updated_at`) VALUES
('计算机科学', 'computer', 'https://picsum.photos/seed/cat_cs/400/300', '编程、算法、数据结构等', NULL, 1, NOW(), NOW()),
('数学', 'math', 'https://picsum.photos/seed/cat_math/400/300', '高等数学、线性代数、概率论', NULL, 2, NOW(), NOW()),
('物理', 'physics', 'https://picsum.photos/seed/cat_physics/400/300', '力学、电磁学、量子物理', NULL, 3, NOW(), NOW()),
('化学', 'chemistry', 'https://picsum.photos/seed/cat_chem/400/300', '有机化学、无机化学', NULL, 4, NOW(), NOW()),
('外语学习', 'language', 'https://picsum.photos/seed/cat_lang/400/300', '英语、日语、法语等', NULL, 5, NOW(), NOW()),
('经济管理', 'economics', 'https://picsum.photos/seed/cat_econ/400/300', '经济学、管理学、金融', NULL, 6, NOW(), NOW());

-- 插入子分类
INSERT INTO `categories` (`name`, `icon`, `image`, `description`, `parent_id`, `sort`, `created_at`, `updated_at`) VALUES
('编程语言', 'code', 'https://picsum.photos/seed/cat_prog/400/300', 'Python、Java、C++等', 1, 1, NOW(), NOW()),
('算法与数据结构', 'algorithm', 'https://picsum.photos/seed/cat_algo/400/300', '算法设计、数据结构', 1, 2, NOW(), NOW()),
('人工智能', 'ai', 'https://picsum.photos/seed/cat_ai/400/300', '机器学习、深度学习', 1, 3, NOW(), NOW()),
('高等数学', 'calculus', 'https://picsum.photos/seed/cat_hmath/400/300', '微积分、数学分析', 2, 1, NOW(), NOW()),
('线性代数', 'linear', 'https://picsum.photos/seed/cat_linear/400/300', '矩阵、向量空间', 2, 2, NOW(), NOW()),
('概率论与统计', 'probability', 'https://picsum.photos/seed/cat_prob/400/300', '概率、统计推断', 2, 3, NOW(), NOW()),
('力学', 'mechanics', 'https://picsum.photos/seed/cat_mech/400/300', '经典力学、分析力学', 3, 1, NOW(), NOW()),
('电磁学', 'electromagnetic', 'https://picsum.photos/seed/cat_em/400/300', '电动力学、电磁场', 3, 2, NOW(), NOW()),
('有机化学', 'organic', 'https://picsum.photos/seed/cat_org/400/300', '有机反应、合成', 4, 1, NOW(), NOW()),
('无机化学', 'inorganic', 'https://picsum.photos/seed/cat_inorg/400/300', '无机材料、配位化学', 4, 2, NOW(), NOW()),
('英语', 'english', 'https://picsum.photos/seed/cat_en/400/300', '四六级、考研英语', 5, 1, NOW(), NOW()),
('日语', 'japanese', 'https://picsum.photos/seed/cat_jp/400/300', 'JLPT考试', 5, 2, NOW(), NOW()),
('经济学', 'economy', 'https://picsum.photos/seed/cat_eco/400/300', '微观、宏观经济学', 6, 1, NOW(), NOW()),
('管理学', 'management', 'https://picsum.photos/seed/cat_mgt/400/300', '企业管理、项目管理', 6, 2, NOW(), NOW());

-- 插入资源
INSERT INTO `resources` (`title`, `description`, `user_id`, `category_id`, `type`, `cover`, `file_path`, `file_name`, `file_size`, `file_ext`, `download_count`, `view_count`, `rating`, `rating_count`, `status`, `is_featured`, `tags`, `created_at`, `updated_at`) VALUES
('Python编程基础教程', '这是一套完整的Python编程基础教程，适合零基础学员学习。', 2, 7, 'document', 'https://picsum.photos/seed/res1/400/300', 'resources/python_basics.pdf', 'Python编程基础教程.pdf', 5242880, 'pdf', 256, 1523, 4.8, 45, 'approved', 1, 'Python,编程,入门', NOW(), NOW()),
('数据结构与算法分析', '详细讲解常见数据结构与算法，包含大量实例代码。', 2, 8, 'video', 'https://picsum.photos/seed/res2/400/300', 'resources/dsa_course.mp4', '数据结构与算法分析.mp4', 524288000, 'mp4', 189, 2341, 4.6, 38, 'approved', 1, '数据结构,算法', NOW(), NOW()),
('深度学习入门指南', '深度学习入门教程，从基础概念到实际应用。', 2, 9, 'document', 'https://picsum.photos/seed/res3/400/300', 'resources/dl_intro.pdf', '深度学习入门指南.pdf', 8388608, 'pdf', 312, 2856, 4.9, 52, 'approved', 1, '深度学习,AI', NOW(), NOW()),
('高等数学期末复习资料', '高等数学期末复习重点知识总结。', 3, 10, 'document', 'https://picsum.photos/seed/res4/400/300', 'resources/math_review.pdf', '高等数学期末复习.pdf', 2097152, 'pdf', 456, 4521, 4.5, 67, 'approved', 0, '高数,复习', NOW(), NOW()),
('线性代数经典习题集', '线性代数经典习题集，涵盖矩阵运算等内容。', 3, 11, 'document', 'https://picsum.photos/seed/res5/400/300', 'resources/la_exercises.pdf', '线性代数习题集.pdf', 3145728, 'pdf', 278, 2134, 4.4, 41, 'approved', 0, '线性代数,习题', NOW(), NOW()),
('大学物理实验报告模板', '大学物理实验报告模板。', 4, 13, 'document', 'https://picsum.photos/seed/res6/400/300', 'resources/physics_template.docx', '物理实验报告模板.docx', 1048576, 'docx', 189, 1567, 4.3, 29, 'approved', 0, '物理,实验', NOW(), NOW()),
('有机化学反应机理详解', '有机化学反应机理详细讲解。', 4, 15, 'video', 'https://picsum.photos/seed/res7/400/300', 'resources/org_chem.mp4', '有机化学反应机理.mp4', 314572800, 'mp4', 145, 1234, 4.7, 23, 'approved', 0, '有机化学,反应', NOW(), NOW()),
('英语四六级高频词汇', '英语四六级高频词汇汇总。', 2, 17, 'audio', 'https://picsum.photos/seed/res8/400/300', 'resources/cet_vocab.mp3', '四六级高频词汇.mp3', 52428800, 'mp3', 567, 6234, 4.6, 89, 'approved', 1, '英语,四六级', NOW(), NOW()),
('微观经济学课堂笔记', '微观经济学完整课堂笔记。', 2, 19, 'document', 'https://picsum.photos/seed/res9/400/300', 'resources/micro_econ.pdf', '微观经济学笔记.pdf', 4194304, 'pdf', 234, 2134, 4.5, 34, 'approved', 0, '经济学,微观', NOW(), NOW()),
('Java企业级开发实战', 'Java企业级开发实战教程。', 2, 7, 'video', 'https://picsum.photos/seed/res10/400/300', 'resources/java_dev.mp4', 'Java企业级开发.mp4', 734003200, 'mp4', 389, 3456, 4.8, 56, 'approved', 1, 'Java,企业级', NOW(), NOW());

-- 插入评论
INSERT INTO `comments` (`user_id`, `resource_id`, `content`, `created_at`, `updated_at`) VALUES
(5, 1, '非常棒的教程，学到了很多！', NOW(), NOW()),
(6, 1, '内容很详细，谢谢分享！', NOW(), NOW()),
(5, 2, '算法讲解得很清楚', NOW(), NOW()),
(7, 3, '深度学习入门必看', NOW(), NOW()),
(6, 4, '高数复习必备', NOW(), NOW()),
(8, 5, '习题很全面', NOW(), NOW()),
(5, 8, '背单词的好帮手', NOW(), NOW()),
(7, 10, 'Java实战教程很实用', NOW(), NOW()),
(9, 2, '数据结构终于搞懂了', NOW(), NOW()),
(8, 3, 'AI入门推荐', NOW(), NOW());

-- 插入评分
INSERT INTO `ratings` (`user_id`, `resource_id`, `score`, `created_at`, `updated_at`) VALUES
(5, 1, 5, NOW(), NOW()),
(6, 1, 4, NOW(), NOW()),
(7, 1, 5, NOW(), NOW()),
(5, 2, 4, NOW(), NOW()),
(6, 2, 5, NOW(), NOW()),
(8, 2, 4, NOW(), NOW()),
(5, 3, 5, NOW(), NOW()),
(6, 3, 5, NOW(), NOW()),
(7, 3, 4, NOW(), NOW()),
(9, 3, 5, NOW(), NOW()),
(5, 4, 4, NOW(), NOW()),
(6, 4, 5, NOW(), NOW()),
(7, 4, 4, NOW(), NOW()),
(8, 5, 4, NOW(), NOW()),
(5, 5, 5, NOW(), NOW()),
(6, 8, 5, NOW(), NOW()),
(7, 8, 4, NOW(), NOW()),
(8, 8, 5, NOW(), NOW()),
(5, 10, 5, NOW(), NOW()),
(6, 10, 4, NOW(), NOW()),
(7, 10, 5, NOW(), NOW()),
(9, 10, 5, NOW(), NOW());

-- 插入收藏
INSERT INTO `favorites` (`user_id`, `resource_id`, `created_at`, `updated_at`) VALUES
(5, 1, NOW(), NOW()),
(5, 2, NOW(), NOW()),
(5, 3, NOW(), NOW()),
(6, 1, NOW(), NOW()),
(6, 4, NOW(), NOW()),
(7, 2, NOW(), NOW()),
(7, 3, NOW(), NOW()),
(8, 5, NOW(), NOW()),
(9, 3, NOW(), NOW()),
(9, 10, NOW(), NOW());

-- 插入下载记录
INSERT INTO `downloads` (`user_id`, `resource_id`, `ip`, `created_at`, `updated_at`) VALUES
(5, 1, '192.168.1.101', NOW(), NOW()),
(5, 2, '192.168.1.101', NOW(), NOW()),
(5, 3, '192.168.1.101', NOW(), NOW()),
(6, 1, '192.168.1.102', NOW(), NOW()),
(6, 4, '192.168.1.102', NOW(), NOW()),
(7, 2, '192.168.1.103', NOW(), NOW()),
(7, 5, '192.168.1.103', NOW(), NOW()),
(8, 3, '192.168.1.104', NOW(), NOW()),
(9, 1, '192.168.1.105', NOW(), NOW()),
(9, 10, '192.168.1.105', NOW(), NOW());

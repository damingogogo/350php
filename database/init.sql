-- 学联界高校教学资源共享平台 数据库初始化脚本
-- 数据库: edu_resource
-- 字符集: utf8mb4

CREATE DATABASE IF NOT EXISTS `edu_resource` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `edu_resource`;

-- 用户表
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
    `nickname` VARCHAR(100) NULL COMMENT '昵称',
    `email` VARCHAR(100) NOT NULL UNIQUE COMMENT '邮箱',
    `password` VARCHAR(255) NOT NULL COMMENT '密码',
    `avatar` VARCHAR(255) NULL COMMENT '头像',
    `role` ENUM('guest', 'student', 'teacher', 'admin') DEFAULT 'student' COMMENT '角色',
    `phone` VARCHAR(20) NULL COMMENT '手机号',
    `school` VARCHAR(100) NULL COMMENT '学校',
    `bio` TEXT NULL COMMENT '个人简介',
    `points` INT DEFAULT 0 COMMENT '积分',
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_role` (`role`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 分类表
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT '分类名称',
    `icon` VARCHAR(255) NULL COMMENT '分类图标',
    `image` VARCHAR(255) NULL COMMENT '分类封面图片',
    `description` VARCHAR(255) NULL COMMENT '分类描述',
    `parent_id` BIGINT UNSIGNED NULL COMMENT '父分类ID',
    `sort` INT DEFAULT 0 COMMENT '排序',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分类表';

-- 资源表
DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL COMMENT '资源标题',
    `description` TEXT NULL COMMENT '资源描述',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '上传者ID',
    `category_id` BIGINT UNSIGNED NOT NULL COMMENT '分类ID',
    `type` ENUM('document', 'video', 'audio', 'image', 'other') DEFAULT 'document' COMMENT '资源类型',
    `cover` VARCHAR(255) NULL COMMENT '封面图片',
    `file_path` VARCHAR(500) NOT NULL COMMENT '文件路径',
    `file_name` VARCHAR(200) NOT NULL COMMENT '文件名',
    `file_size` BIGINT DEFAULT 0 COMMENT '文件大小(字节)',
    `file_ext` VARCHAR(20) NULL COMMENT '文件扩展名',
    `download_count` INT DEFAULT 0 COMMENT '下载次数',
    `view_count` INT DEFAULT 0 COMMENT '浏览次数',
    `rating` DECIMAL(3,2) DEFAULT 0 COMMENT '评分',
    `rating_count` INT DEFAULT 0 COMMENT '评分人数',
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved' COMMENT '状态',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT '是否推荐',
    `tags` VARCHAR(500) NULL COMMENT '标签，逗号分隔',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_download_count` (`download_count`),
    FULLTEXT INDEX `ft_title_desc` (`title`, `description`),
    CONSTRAINT `fk_resources_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_resources_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='资源表';

-- 评论表
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `resource_id` BIGINT UNSIGNED NOT NULL COMMENT '资源ID',
    `content` TEXT NOT NULL COMMENT '评论内容',
    `parent_id` BIGINT UNSIGNED NULL COMMENT '父评论ID',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_resource_id` (`resource_id`),
    INDEX `idx_parent_id` (`parent_id`),
    CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comments_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论表';

-- 评分表
DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `resource_id` BIGINT UNSIGNED NOT NULL COMMENT '资源ID',
    `score` TINYINT NOT NULL COMMENT '评分1-5',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uk_user_resource` (`user_id`, `resource_id`),
    INDEX `idx_resource_id` (`resource_id`),
    CONSTRAINT `fk_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ratings_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评分表';

-- 收藏表
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `resource_id` BIGINT UNSIGNED NOT NULL COMMENT '资源ID',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uk_user_resource` (`user_id`, `resource_id`),
    INDEX `idx_resource_id` (`resource_id`),
    CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_favorites_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收藏表';

-- 下载记录表
DROP TABLE IF EXISTS `downloads`;
CREATE TABLE `downloads` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `resource_id` BIGINT UNSIGNED NOT NULL COMMENT '资源ID',
    `ip` VARCHAR(50) NULL COMMENT '下载IP',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_resource_id` (`resource_id`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_downloads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_downloads_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='下载记录表';

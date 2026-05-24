<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Download;
use App\Models\ExamQuestion;
use App\Models\Favorite;
use App\Models\ForumBoard;
use App\Models\ForumPost;
use App\Models\HomeworkSubmission;
use App\Models\Rating;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@example.com',
                'password' => '123456',
                'nickname' => '超级管理员',
                'role' => 'admin',
                'school' => '华北理工大学轻工学院',
            ]
        );

        $teacher1 = User::firstOrCreate(
            ['username' => 'teacher1'],
            [
                'email' => 'teacher1@example.com',
                'password' => '123456',
                'nickname' => '张老师',
                'role' => 'teacher',
                'school' => '华北理工大学轻工学院',
                'department' => '人工智能学院',
                'title' => '讲师',
                'class_name' => '2022计科6班',
                'bio' => '负责 PHP Web 开发、数据库课程和项目实践指导。',
            ]
        );

        $teacher2 = User::firstOrCreate(
            ['username' => 'teacher2'],
            [
                'email' => 'teacher2@example.com',
                'password' => '123456',
                'nickname' => '李老师',
                'role' => 'teacher',
                'school' => '华北理工大学轻工学院',
                'department' => '人工智能学院',
                'title' => '副教授',
                'class_name' => '2022计科6班',
                'bio' => '负责数据结构、算法分析和综合实训。',
            ]
        );

        $teacher3 = User::firstOrCreate(
            ['username' => 'teacher3'],
            [
                'email' => 'teacher3@example.com',
                'password' => '123456',
                'nickname' => '王老师',
                'role' => 'teacher',
                'school' => '华北理工大学轻工学院',
                'department' => '人工智能学院',
                'title' => '讲师',
                'class_name' => '2022计科5班',
                'bio' => '负责大学英语、公共基础课与考试复习资源建设。',
            ]
        );

        $student1 = User::firstOrCreate(
            ['username' => 'student1'],
            [
                'email' => 'student1@example.com',
                'password' => '123456',
                'nickname' => '白同学',
                'role' => 'student',
                'school' => '华北理工大学轻工学院',
                'department' => '人工智能学院',
                'major' => '计算机科学与技术',
                'class_name' => '2022计科6班',
                'student_id' => '202224430634',
            ]
        );

        $student2 = User::firstOrCreate(
            ['username' => 'student2'],
            [
                'email' => 'student2@example.com',
                'password' => '123456',
                'nickname' => '王同学',
                'role' => 'student',
                'school' => '华北理工大学轻工学院',
                'department' => '人工智能学院',
                'major' => '计算机科学与技术',
                'class_name' => '2022计科5班',
            ]
        );

        $categories = collect([
            ['name' => '程序设计', 'description' => 'PHP、Java、Python、前端开发等课程资料', 'sort' => 1],
            ['name' => '数据库技术', 'description' => 'MySQL、SQL 练习、数据库课程设计资料', 'sort' => 2],
            ['name' => '算法与数据结构', 'description' => '算法分析、数据结构课件与题解', 'sort' => 3],
            ['name' => '公共基础课', 'description' => '高等数学、大学英语、物理实验等', 'sort' => 4],
            ['name' => '历年题库', 'description' => '真题、模拟题、重点练习和解析', 'sort' => 5],
            ['name' => '教学案例库', 'description' => '课堂案例、代码片段和补充材料', 'sort' => 6],
        ])->map(function ($data) {
            return Category::firstOrCreate(['name' => $data['name']], $data);
        });

        $resourceRows = [
            [
                'title' => 'PHP Web 开发预习课件',
                'description' => '包含 PHP 基础语法、Laravel 路由与 MVC 结构说明，适合课前预习。',
                'course_name' => 'PHP Web开发',
                'category_id' => $categories[0]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'ppt',
                'file_ext' => 'pptx',
                'file_name' => 'PHP Web 开发预习课件.pptx',
                'file_path' => 'samples/php-web-preview.pptx',
                'share_scope' => 'class',
                'class_name' => '2022计科6班',
                'preview_note' => '请提前阅读 MVC 示例，并准备一个路由设计问题带到课堂。',
                'download_count' => 46,
                'view_count' => 238,
                'rating' => 4.8,
                'rating_count' => 16,
                'is_featured' => true,
                'tags' => 'PHP,Laravel,预习课件',
            ],
            [
                'title' => 'MySQL 数据库设计规范',
                'description' => '整理数据库表设计、索引、外键、命名规范和课程设计常见错误。',
                'course_name' => '数据库原理',
                'category_id' => $categories[1]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => 'MySQL 数据库设计规范.pdf',
                'file_path' => 'samples/mysql-design.pdf',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '可作为课程设计数据库部分的检查清单。',
                'download_count' => 128,
                'view_count' => 617,
                'rating' => 4.7,
                'rating_count' => 31,
                'is_featured' => true,
                'tags' => 'MySQL,课程设计,PDF',
            ],
            [
                'title' => '数据结构课堂案例代码',
                'description' => '教师团队共享的链表、栈、队列、树结构课堂案例代码包。',
                'course_name' => '数据结构',
                'category_id' => $categories[2]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'archive',
                'file_ext' => 'zip',
                'file_name' => '数据结构课堂案例代码.zip',
                'file_path' => 'samples/data-structure-cases.zip',
                'share_scope' => 'teachers',
                'class_name' => null,
                'preview_note' => '供同课程教师二次备课和统一案例素材使用。',
                'download_count' => 35,
                'view_count' => 184,
                'rating' => 4.5,
                'rating_count' => 9,
                'is_featured' => false,
                'tags' => '数据结构,教师共享,代码',
            ],
            [
                'title' => '大学英语四级听力训练',
                'description' => '包含四级听力训练音频和课堂听写任务。',
                'course_name' => '大学英语',
                'category_id' => $categories[3]->id,
                'user_id' => $teacher1->id,
                'type' => 'audio',
                'file_type' => 'audio',
                'file_ext' => 'mp3',
                'file_name' => '四级听力训练.mp3',
                'file_path' => 'samples/cet-listening.mp3',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '建议课前完成第一段听写。',
                'download_count' => 92,
                'view_count' => 406,
                'rating' => 4.6,
                'rating_count' => 22,
                'is_featured' => false,
                'tags' => '英语,音频,四级',
            ],
            [
                'title' => 'Web 项目部署演示视频',
                'description' => '从本地开发到服务器部署的完整演示，包含环境配置和常见错误处理。',
                'course_name' => '综合项目实训',
                'category_id' => $categories[5]->id,
                'user_id' => $teacher1->id,
                'type' => 'video',
                'file_type' => 'video',
                'file_ext' => 'mp4',
                'file_name' => 'Web 项目部署演示视频.mp4',
                'file_path' => 'samples/web-deploy.mp4',
                'share_scope' => 'class',
                'class_name' => '2022计科6班',
                'preview_note' => '观看后整理部署步骤截图。',
                'download_count' => 74,
                'view_count' => 533,
                'rating' => 4.9,
                'rating_count' => 27,
                'is_featured' => true,
                'tags' => '部署,视频,实训',
            ],
            [
                'title' => '实验报告模板',
                'description' => '通用实验报告 Word 模板，包含封面、目录、实验步骤和总结部分。',
                'course_name' => '课程实验',
                'category_id' => $categories[3]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => '实验报告模板.docx',
                'file_path' => 'samples/report-template.docx',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '下载后按课程要求替换封面信息。',
                'download_count' => 211,
                'view_count' => 940,
                'rating' => 4.4,
                'rating_count' => 38,
                'is_featured' => false,
                'tags' => 'Word,模板,实验报告',
            ],
        ];

        $additionalResourceRows = [
            [
                'title' => 'Laravel 路由与控制器课堂课件',
                'description' => '围绕 Web 路由、控制器分层、请求响应流程整理的课堂讲义。',
                'course_name' => 'PHP Web开发',
                'category_id' => $categories[0]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'ppt',
                'file_ext' => 'pptx',
                'file_name' => 'Laravel 路由与控制器课堂课件.pptx',
                'file_path' => 'samples/laravel-route-controller.pptx',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '课前完成路由参数和资源控制器预习。',
                'download_count' => 67,
                'view_count' => 330,
                'rating' => 4.7,
                'rating_count' => 18,
                'is_featured' => true,
                'tags' => 'PHP,Laravel,控制器,课件',
            ],
            [
                'title' => 'PHP 表单验证与文件上传案例',
                'description' => '包含表单校验、文件类型判断、上传错误处理和存储路径设计。',
                'course_name' => 'PHP Web开发',
                'category_id' => $categories[0]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => 'PHP 表单验证与文件上传案例.docx',
                'file_path' => 'samples/php-upload-validation.docx',
                'share_scope' => 'teachers',
                'class_name' => null,
                'preview_note' => '教师备课时可直接作为实训任务说明使用。',
                'download_count' => 42,
                'view_count' => 210,
                'rating' => 4.6,
                'rating_count' => 12,
                'is_featured' => false,
                'tags' => 'PHP,上传,验证,教师共享',
            ],
            [
                'title' => 'MySQL 索引优化课堂讲义',
                'description' => '整理索引设计原则、Explain 查看方法和常见慢查询优化案例。',
                'course_name' => '数据库原理',
                'category_id' => $categories[1]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'ppt',
                'file_ext' => 'pptx',
                'file_name' => 'MySQL 索引优化课堂讲义.pptx',
                'file_path' => 'samples/mysql-index-lesson.pptx',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '结合课程设计数据库部分进行复习。',
                'download_count' => 88,
                'view_count' => 438,
                'rating' => 4.8,
                'rating_count' => 22,
                'is_featured' => true,
                'tags' => 'MySQL,索引,数据库',
            ],
            [
                'title' => '数据库课程设计检查表',
                'description' => '用于检查表结构、字段类型、外键约束、索引和数据完整性。',
                'course_name' => '数据库原理',
                'category_id' => $categories[1]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => '数据库课程设计检查表.pdf',
                'file_path' => 'samples/database-project-checklist.pdf',
                'share_scope' => 'class',
                'class_name' => '2022计科6班',
                'preview_note' => '提交课程设计前逐项核对。',
                'download_count' => 73,
                'view_count' => 360,
                'rating' => 4.5,
                'rating_count' => 14,
                'is_featured' => false,
                'tags' => '数据库,课程设计,PDF',
            ],
            [
                'title' => '数据结构树与图专题课件',
                'description' => '覆盖二叉树、遍历、图的存储结构和最短路径算法。',
                'course_name' => '数据结构',
                'category_id' => $categories[2]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'ppt',
                'file_ext' => 'pptx',
                'file_name' => '数据结构树与图专题课件.pptx',
                'file_path' => 'samples/tree-graph-lesson.pptx',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '重点复习遍历顺序和图的邻接表表示。',
                'download_count' => 96,
                'view_count' => 512,
                'rating' => 4.9,
                'rating_count' => 26,
                'is_featured' => true,
                'tags' => '数据结构,树,图,课件',
            ],
            [
                'title' => '算法复杂度速查手册',
                'description' => '整理常用算法复杂度、适用场景和考试常见问法。',
                'course_name' => '算法分析',
                'category_id' => $categories[2]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => '算法复杂度速查手册.pdf',
                'file_path' => 'samples/algorithm-complexity-guide.pdf',
                'share_scope' => 'teachers',
                'class_name' => null,
                'preview_note' => '教师可用于统一复习提纲。',
                'download_count' => 54,
                'view_count' => 241,
                'rating' => 4.4,
                'rating_count' => 10,
                'is_featured' => false,
                'tags' => '算法,复杂度,教师共享',
            ],
            [
                'title' => '高等数学极限与导数练习',
                'description' => '公共基础课练习文档，包含极限、连续性和导数应用题。',
                'course_name' => '高等数学',
                'category_id' => $categories[3]->id,
                'user_id' => $teacher3->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => '高等数学极限与导数练习.docx',
                'file_path' => 'samples/math-limit-derivative.docx',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '建议考试周前完成并订正错题。',
                'download_count' => 114,
                'view_count' => 576,
                'rating' => 4.6,
                'rating_count' => 24,
                'is_featured' => false,
                'tags' => '高等数学,练习,Word',
            ],
            [
                'title' => '大学英语四级阅读技巧课件',
                'description' => '讲解阅读定位、长难句分析和选项排除方法。',
                'course_name' => '大学英语',
                'category_id' => $categories[3]->id,
                'user_id' => $teacher3->id,
                'type' => 'document',
                'file_type' => 'ppt',
                'file_ext' => 'pptx',
                'file_name' => '大学英语四级阅读技巧课件.pptx',
                'file_path' => 'samples/cet-reading-skills.pptx',
                'share_scope' => 'class',
                'class_name' => '2022计科5班',
                'preview_note' => '配合听力训练资源完成整套复习。',
                'download_count' => 61,
                'view_count' => 288,
                'rating' => 4.3,
                'rating_count' => 11,
                'is_featured' => false,
                'tags' => '英语,四级,阅读,课件',
            ],
            [
                'title' => '历年 PHP Web 期末题汇编',
                'description' => '按知识点整理 PHP Web 开发历年期末题和参考答案。',
                'course_name' => 'PHP Web开发',
                'category_id' => $categories[4]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => '历年 PHP Web 期末题汇编.pdf',
                'file_path' => 'samples/php-final-papers.pdf',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '考试前重点复习 MVC、数据库和文件上传。',
                'download_count' => 152,
                'view_count' => 801,
                'rating' => 4.9,
                'rating_count' => 33,
                'is_featured' => true,
                'tags' => 'PHP,真题,复习',
            ],
            [
                'title' => '综合项目实训部署材料包',
                'description' => '包含部署步骤、环境变量示例、Nginx 配置和常见错误说明。',
                'course_name' => '综合项目实训',
                'category_id' => $categories[5]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'archive',
                'file_ext' => 'zip',
                'file_name' => '综合项目实训部署材料包.zip',
                'file_path' => 'samples/project-deploy-kit.zip',
                'share_scope' => 'teachers',
                'class_name' => null,
                'preview_note' => '同课程教师可按班级情况调整部署任务。',
                'download_count' => 39,
                'view_count' => 198,
                'rating' => 4.2,
                'rating_count' => 8,
                'is_featured' => false,
                'tags' => '部署,实训,资源包',
            ],
            [
                'title' => 'PHP Web 登录注册模块教学设计',
                'description' => '围绕用户表设计、登录校验、注册表单、角色区分和会话维护整理的课堂教学设计文档。',
                'course_name' => 'PHP Web开发',
                'category_id' => $categories[0]->id,
                'user_id' => $teacher3->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => 'PHP Web 登录注册模块教学设计.docx',
                'file_path' => 'samples/php-login-register-plan.docx',
                'share_scope' => 'teachers',
                'class_name' => null,
                'preview_note' => '教师可按本班基础调整表单校验和角色权限讲解顺序。',
                'download_count' => 47,
                'view_count' => 224,
                'rating' => 4.5,
                'rating_count' => 13,
                'is_featured' => false,
                'tags' => 'PHP,登录注册,教学设计',
            ],
            [
                'title' => 'PHP Web 资源下载流程图与说明',
                'description' => '用流程图说明资源可见性判断、下载记录写入、文件响应和异常提示处理。',
                'course_name' => 'PHP Web开发',
                'category_id' => $categories[0]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => 'PHP Web 资源下载流程图与说明.pdf',
                'file_path' => 'samples/php-download-flow.pdf',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '建议结合平台资源详情页和下载按钮进行代码阅读。',
                'download_count' => 83,
                'view_count' => 410,
                'rating' => 4.8,
                'rating_count' => 19,
                'is_featured' => true,
                'tags' => 'PHP,下载,权限,PDF',
            ],
            [
                'title' => 'MySQL 课程设计答辩问题清单',
                'description' => '整理数据库课程设计答辩常问问题，包括表结构说明、索引选择、数据完整性和查询优化。',
                'course_name' => '数据库原理',
                'category_id' => $categories[1]->id,
                'user_id' => $teacher3->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => 'MySQL 课程设计答辩问题清单.docx',
                'file_path' => 'samples/mysql-defense-questions.docx',
                'share_scope' => 'class',
                'class_name' => '2022计科5班',
                'preview_note' => '答辩前按问题清单逐项准备截图、SQL 和说明文字。',
                'download_count' => 69,
                'view_count' => 365,
                'rating' => 4.6,
                'rating_count' => 16,
                'is_featured' => false,
                'tags' => 'MySQL,答辩,课程设计',
            ],
            [
                'title' => '数据库 SQL 查询训练题',
                'description' => '包含单表查询、多表连接、分组统计、子查询和排序分页的训练题。',
                'course_name' => '数据库原理',
                'category_id' => $categories[1]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => '数据库 SQL 查询训练题.pdf',
                'file_path' => 'samples/sql-query-practice.pdf',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '先独立写 SQL，再对照课堂讲义检查字段、条件和排序。',
                'download_count' => 122,
                'view_count' => 604,
                'rating' => 4.7,
                'rating_count' => 28,
                'is_featured' => true,
                'tags' => 'SQL,练习,数据库',
            ],
            [
                'title' => '数据结构排序算法动画说明',
                'description' => '使用视频演示冒泡、选择、插入、快速排序的执行过程和复杂度差异。',
                'course_name' => '数据结构',
                'category_id' => $categories[2]->id,
                'user_id' => $teacher1->id,
                'type' => 'video',
                'file_type' => 'video',
                'file_ext' => 'mp4',
                'file_name' => '数据结构排序算法动画说明.mp4',
                'file_path' => 'samples/sorting-animation.mp4',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '观看时记录每种排序的比较次数和移动次数变化。',
                'download_count' => 76,
                'view_count' => 489,
                'rating' => 4.6,
                'rating_count' => 17,
                'is_featured' => false,
                'tags' => '数据结构,排序,视频',
            ],
            [
                'title' => '数据结构链表实验指导书',
                'description' => '包含单链表创建、插入、删除、查找和实验报告提交要求。',
                'course_name' => '数据结构',
                'category_id' => $categories[2]->id,
                'user_id' => $teacher3->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => '数据结构链表实验指导书.docx',
                'file_path' => 'samples/linked-list-lab.docx',
                'share_scope' => 'class',
                'class_name' => '2022计科5班',
                'preview_note' => '实验前先画出链表指针变化图，再编写代码。',
                'download_count' => 58,
                'view_count' => 299,
                'rating' => 4.4,
                'rating_count' => 12,
                'is_featured' => false,
                'tags' => '数据结构,链表,实验指导',
            ],
            [
                'title' => '大学英语写作模板与范文',
                'description' => '整理四级写作常用句型、段落模板、范文拆解和易错表达。',
                'course_name' => '大学英语',
                'category_id' => $categories[3]->id,
                'user_id' => $teacher3->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => '大学英语写作模板与范文.pdf',
                'file_path' => 'samples/cet-writing-template.pdf',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '背诵模板前先理解段落功能，避免机械套用。',
                'download_count' => 145,
                'view_count' => 711,
                'rating' => 4.7,
                'rating_count' => 32,
                'is_featured' => true,
                'tags' => '英语,写作,PDF',
            ],
            [
                'title' => '高等数学期末复习提纲',
                'description' => '按函数、极限、导数、积分和应用题整理期末复习提纲。',
                'course_name' => '高等数学',
                'category_id' => $categories[4]->id,
                'user_id' => $teacher2->id,
                'type' => 'document',
                'file_type' => 'word',
                'file_ext' => 'docx',
                'file_name' => '高等数学期末复习提纲.docx',
                'file_path' => 'samples/math-final-outline.docx',
                'share_scope' => 'platform',
                'class_name' => null,
                'preview_note' => '建议按章节做错题归类，并标记公式使用条件。',
                'download_count' => 101,
                'view_count' => 538,
                'rating' => 4.5,
                'rating_count' => 21,
                'is_featured' => false,
                'tags' => '高等数学,期末,复习',
            ],
            [
                'title' => '软件项目案例说明书',
                'description' => '展示教学资源平台、学生信息管理、在线题库等项目案例的需求分析和模块划分。',
                'course_name' => '综合项目实训',
                'category_id' => $categories[5]->id,
                'user_id' => $teacher1->id,
                'type' => 'document',
                'file_type' => 'pdf',
                'file_ext' => 'pdf',
                'file_name' => '软件项目案例说明书.pdf',
                'file_path' => 'samples/software-project-cases.pdf',
                'share_scope' => 'teachers',
                'class_name' => null,
                'preview_note' => '教师可从案例中选择一个作为课程设计分组项目。',
                'download_count' => 66,
                'view_count' => 347,
                'rating' => 4.3,
                'rating_count' => 14,
                'is_featured' => false,
                'tags' => '项目案例,课程设计,教师共享',
            ],
        ];

        $resourceRows = array_merge($resourceRows, $additionalResourceRows);

        foreach ($resourceRows as $row) {
            Resource::updateOrCreate(
                ['title' => $row['title']],
                array_merge([
                    'file_size' => 1024 * 1024 * 6,
                    'status' => 'approved',
                    'cover' => null,
                ], $row)
            );
        }

        $this->ensureSampleResourceFiles($resourceRows);

        Announcement::updateOrCreate(
            ['title' => '平台资源发布规范'],
            [
                'content' => '教师发布资源时请填写课程名称、文件格式和共享范围，避免学生获取到错误版本。',
                'publisher_id' => $admin->id,
                'publisher_role' => 'admin',
                'target_role' => 'all',
                'is_slider' => true,
                'sort' => 10,
                'status' => 'published',
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'PHP Web 开发第六周预习安排'],
            [
                'content' => '请 2022计科6班同学课前下载预习课件，重点阅读路由、控制器和视图部分。',
                'publisher_id' => $teacher1->id,
                'publisher_role' => 'teacher',
                'target_role' => 'student',
                'is_slider' => false,
                'sort' => 5,
                'status' => 'published',
            ]
        );

        foreach ([
            [
                'title' => '平台资源审核与下载说明',
                'content' => '所有教师上传的资源需要标明文件格式、课程名称、共享范围和适用班级。学生下载前请先进入详情页查看学习目标、资源目录和教师说明，若文件无法打开可在评论区反馈或联系管理员。',
                'publisher_id' => $admin->id,
                'publisher_role' => 'admin',
                'target_role' => 'all',
                'is_slider' => false,
                'sort' => 8,
            ],
            [
                'title' => '系统平台管理员数据备份提醒',
                'content' => '管理员后台已提供用户、资源、公告、题库、资源池、评论、收藏、下载和评分等核心数据导出功能。建议在课程验收前导出一次备份，确保演示数据完整。',
                'publisher_id' => $admin->id,
                'publisher_role' => 'admin',
                'target_role' => 'teacher',
                'is_slider' => false,
                'sort' => 7,
            ],
            [
                'title' => '数据库课程设计资料更新',
                'content' => '数据库课程设计新增 SQL 查询训练题、答辩问题清单和数据库检查表。请同学们按表结构、外键约束、索引设计和测试数据四部分完成自查。',
                'publisher_id' => $teacher2->id,
                'publisher_role' => 'teacher',
                'target_role' => 'student',
                'is_slider' => false,
                'sort' => 6,
            ],
            [
                'title' => '大学英语四级复习资源包说明',
                'content' => '四级复习资源已补充听力训练、阅读技巧课件、写作模板与范文。建议按听力、阅读、写作三个模块分阶段完成练习，并在资源详情页提交学习反馈。',
                'publisher_id' => $teacher3->id,
                'publisher_role' => 'teacher',
                'target_role' => 'student',
                'is_slider' => false,
                'sort' => 5,
            ],
        ] as $announcement) {
            Announcement::updateOrCreate(
                ['title' => $announcement['title']],
                array_merge($announcement, ['status' => 'published'])
            );
        }

        foreach ([
            ['subject_name' => 'PHP Web开发', 'paper_name' => '2025期末A卷', 'question_type' => '历年真题', 'question' => '简述 MVC 架构中 Model、View、Controller 的职责。', 'answer' => 'Model 负责数据与业务规则，View 负责展示，Controller 负责请求调度。', 'analysis' => '回答时应结合一次请求从路由到页面输出的流程。', 'difficulty' => '★★'],
            ['subject_name' => '数据库原理', 'paper_name' => '重点练习', 'question_type' => '重点练习', 'question' => '说明主键、外键和索引的区别。', 'answer' => '主键唯一标识记录，外键维护表关系，索引用于提升查询效率。', 'analysis' => '可结合课程设计中的 users 与 resources 表说明。', 'difficulty' => '★'],
            ['subject_name' => '数据结构', 'paper_name' => '模拟试卷', 'question_type' => '模拟试卷', 'question' => '写出队列先进先出的应用场景。', 'answer' => '任务调度、打印队列、消息队列等。', 'analysis' => '重点说明入队和出队顺序。', 'difficulty' => '★'],
            ['subject_name' => 'PHP Web开发', 'paper_name' => '2024期末B卷', 'question_type' => '历年真题', 'question' => 'Laravel 中资源路由适合解决哪类业务问题？', 'answer' => '适合处理围绕同一资源的增删改查业务，例如教学资源的列表、详情、发布、更新和删除。', 'analysis' => '答题时要说明 RESTful 风格和控制器方法之间的对应关系。', 'difficulty' => '★★'],
            ['subject_name' => 'PHP Web开发', 'paper_name' => '重点练习', 'question_type' => '重点练习', 'question' => '上传资源时为什么要校验文件类型和文件大小？', 'answer' => '为了避免恶意文件上传、节省服务器空间，并保证资源分类准确。', 'analysis' => '可结合平台要求 Word、PDF、音频、视频分类说明。', 'difficulty' => '★'],
            ['subject_name' => '数据库原理', 'paper_name' => '2025模拟卷', 'question_type' => '模拟试卷', 'question' => '如何设计 resources 表以支持资源类型和共享范围？', 'answer' => '可增加 file_type、share_scope、class_name 等字段，并通过外键关联用户和分类。', 'analysis' => '重点考查字段设计与业务规则的对应关系。', 'difficulty' => '★★'],
            ['subject_name' => '数据结构', 'paper_name' => '2024期末A卷', 'question_type' => '历年真题', 'question' => '二叉树先序、中序、后序遍历的访问顺序分别是什么？', 'answer' => '先序：根左右；中序：左根右；后序：左右根。', 'analysis' => '可用一个三层二叉树举例说明遍历过程。', 'difficulty' => '★★'],
            ['subject_name' => '高等数学', 'paper_name' => '重点练习', 'question_type' => '重点练习', 'question' => '函数在一点连续需要满足哪些条件？', 'answer' => '函数在该点有定义，极限存在，且极限值等于函数值。', 'analysis' => '注意左极限和右极限需要相等。', 'difficulty' => '★'],
            ['subject_name' => '大学英语', 'paper_name' => '四级模拟卷', 'question_type' => '模拟试卷', 'question' => '阅读理解中如何快速定位细节题答案？', 'answer' => '先圈出题干关键词，再回到原文查找同义替换或相近表达。', 'analysis' => '重点训练定位和排除干扰项。', 'difficulty' => '★'],
            ['subject_name' => 'PHP Web开发', 'paper_name' => '2026模拟卷', 'question_type' => '模拟试卷', 'question' => '如何在教学资源平台中实现教师、学生和管理员三类用户的权限区分？', 'answer' => '可在 users 表中设置 role 字段，并在控制器中根据角色限制资源发布、下载、审核和后台访问。', 'analysis' => '答题要结合角色字段、路由中间件、控制器授权判断和页面入口展示。', 'difficulty' => '★★★'],
            ['subject_name' => '数据库原理', 'paper_name' => '重点练习', 'question_type' => '重点练习', 'question' => '资源表为什么需要同时保存 file_type、file_ext 和 file_path？', 'answer' => 'file_type 用于业务分类和筛选，file_ext 保存真实扩展名，file_path 用于服务器定位下载文件。', 'analysis' => '该题考查数据库字段与业务功能之间的对应关系。', 'difficulty' => '★★'],
            ['subject_name' => '数据结构', 'paper_name' => '2026模拟卷', 'question_type' => '模拟试卷', 'question' => '顺序表和链表在插入、删除、随机访问上的时间复杂度有什么区别？', 'answer' => '顺序表随机访问为 O(1)，插入删除通常为 O(n)；链表查找为 O(n)，已知节点位置时插入删除可为 O(1)。', 'analysis' => '答题时要说明是否已经定位到节点位置。', 'difficulty' => '★★'],
            ['subject_name' => '高等数学', 'paper_name' => '2025期末A卷', 'question_type' => '历年真题', 'question' => '求导数应用题时，如何判断极值点和拐点？', 'answer' => '极值点通常结合一阶导数符号变化判断，拐点结合二阶导数符号变化判断。', 'analysis' => '要注意导数不存在的点也可能成为极值候选点。', 'difficulty' => '★★'],
            ['subject_name' => '大学英语', 'paper_name' => '重点练习', 'question_type' => '重点练习', 'question' => '写作开头段如何做到观点明确且不过度模板化？', 'answer' => '先改写题目背景，再明确表达个人观点，最后用一句话引出下文理由。', 'analysis' => '避免只堆固定句型，要让关键词和题目语境对应。', 'difficulty' => '★'],
        ] as $question) {
            ExamQuestion::updateOrCreate(
                ['subject_name' => $question['subject_name'], 'question' => $question['question']],
                array_merge($question, ['teacher_id' => $teacher1->id])
            );
        }

        $boards = [
            ['code' => 'course-share', 'name' => '课程共建资源池', 'description' => '同课程教师共享课件、案例、代码和参考资料。'],
            ['code' => 'student-help', 'name' => '学生学习互助', 'description' => '学生围绕资源下载、复习资料和作业问题进行交流。'],
            ['code' => 'exam-review', 'name' => '考试复习专区', 'description' => '整理历年题目、模拟试卷和重点解析。'],
        ];

        foreach ($boards as $index => $board) {
            ForumBoard::updateOrCreate(
                ['code' => $board['code']],
                array_merge($board, ['moderator_id' => $admin->id, 'sort' => $index + 1])
            );
        }

        $courseBoard = ForumBoard::where('code', 'course-share')->first();
        $studentBoard = ForumBoard::where('code', 'student-help')->first();
        $examBoard = ForumBoard::where('code', 'exam-review')->first();

        foreach ([
            ['board_id' => $courseBoard->id, 'user_id' => $teacher1->id, 'title' => 'PHP 课程第六周案例补充', 'content' => '补充一个资源上传与权限控制的课堂案例，可供教师团队二次备课。', 'post_type' => 'recommended', 'view_count' => 21],
            ['board_id' => $courseBoard->id, 'user_id' => $teacher2->id, 'title' => '数据库课程设计统一检查清单', 'content' => '建议所有班级提交前统一检查表结构、主外键、索引和测试数据，避免课程设计验收时反复返工。', 'post_type' => 'recommended', 'view_count' => 18],
            ['board_id' => $courseBoard->id, 'user_id' => $teacher3->id, 'title' => '公共基础课复习资料共建说明', 'content' => '高数、英语等公共课资料可按章节整理，教师上传后标明适用班级和考试范围。', 'post_type' => 'normal', 'view_count' => 12],
            ['board_id' => $studentBoard->id, 'user_id' => $student1->id, 'title' => 'PHP 文件上传实验运行报错求助', 'content' => '上传文件时提示文件过大，应该调整表单限制还是服务器限制？希望老师和同学帮忙看看。', 'post_type' => 'help', 'view_count' => 26],
            ['board_id' => $studentBoard->id, 'user_id' => $student2->id, 'title' => '数据结构树遍历有没有好记的方法', 'content' => '先序、中序、后序容易混，希望大家分享一下复习口诀和练习资料。', 'post_type' => 'help', 'view_count' => 19],
            ['board_id' => $examBoard->id, 'user_id' => $teacher1->id, 'title' => 'PHP Web 期末复习重点', 'content' => '重点关注 MVC、路由、数据库连接、资源上传下载、用户权限控制和评论评分流程。', 'post_type' => 'urgent', 'view_count' => 45],
            ['board_id' => $examBoard->id, 'user_id' => $teacher2->id, 'title' => '数据库原理考前题型说明', 'content' => '常见题型包括概念解释、SQL 查询、表结构设计、索引分析和规范化判断。', 'post_type' => 'recommended', 'view_count' => 38],
            ['board_id' => $courseBoard->id, 'user_id' => $teacher1->id, 'title' => '教学资源文件格式统一建议', 'content' => '课件建议使用 PPT 或 PDF，实验指导建议使用 Word，代码案例可使用压缩包，听力材料和演示材料分别归为音频、视频。上传时请在标签中写明课程、章节和用途，便于学生检索。', 'post_type' => 'recommended', 'view_count' => 31],
            ['board_id' => $courseBoard->id, 'user_id' => $teacher2->id, 'title' => '课程设计验收资源包组织方式', 'content' => '建议每个课程设计资源包包含需求说明、数据库设计、功能截图、部署步骤和答辩问题清单。教师之间共享的资源可先放到教师共享范围，确认后再开放给全平台师生。', 'post_type' => 'recommended', 'view_count' => 29],
            ['board_id' => $studentBoard->id, 'user_id' => $student1->id, 'title' => '资源详情页里的学习目标怎么使用', 'content' => '现在每个资源详情里都有学习目标、资源目录和教师说明。我的做法是先看学习目标，再下载文件，最后在评论区记录哪个部分还不会。大家还有没有更高效的复习流程？', 'post_type' => 'normal', 'view_count' => 24],
            ['board_id' => $examBoard->id, 'user_id' => $teacher3->id, 'title' => '公共基础课考前复习节奏', 'content' => '高数建议按知识点整理错题，英语建议按听力、阅读、写作三类资源分模块训练。复习时不要只看答案，要把题目解析中的解题步骤写出来。', 'post_type' => 'recommended', 'view_count' => 34],
        ] as $post) {
            ForumPost::updateOrCreate(
                ['title' => $post['title']],
                $post
            );
        }

        $firstResource = Resource::where('title', 'PHP Web 开发预习课件')->first();
        if ($firstResource) {
            Favorite::firstOrCreate(['user_id' => $student1->id, 'resource_id' => $firstResource->id]);
            Comment::firstOrCreate(
                ['user_id' => $student1->id, 'resource_id' => $firstResource->id, 'content' => '预习任务很清楚，课前能先把问题整理出来。']
            );
            Rating::updateOrCreate(
                ['user_id' => $student1->id, 'resource_id' => $firstResource->id],
                ['score' => 5]
            );
            Download::firstOrCreate(
                ['user_id' => $student1->id, 'resource_id' => $firstResource->id],
                ['ip' => '127.0.0.1']
            );
        }

        $teacherLearningSeedPairs = [
            [$teacher1, 'MySQL 数据库设计规范'],
            [$teacher1, '数据结构课堂案例代码'],
            [$teacher1, '课程设计验收答辩问题清单'],
            [$teacher2, 'PHP Web 开发预习课件'],
            [$teacher2, '软件项目案例说明书'],
            [$teacher3, 'PHP 表单验证与文件上传案例'],
            [$teacher3, '高等数学期末复习提纲'],
        ];

        foreach ($teacherLearningSeedPairs as [$teacher, $resourceTitle]) {
            $resource = Resource::where('title', $resourceTitle)->first();

            if (!$resource) {
                continue;
            }

            Favorite::firstOrCreate(['user_id' => $teacher->id, 'resource_id' => $resource->id]);
            Download::firstOrCreate(
                ['user_id' => $teacher->id, 'resource_id' => $resource->id],
                ['ip' => '127.0.0.1']
            );
        }

        HomeworkSubmission::firstOrCreate(
            ['user_id' => $student1->id, 'assignment_title' => '第六周资源上传实验'],
            [
                'teacher_id' => $teacher1->id,
                'course_name' => 'PHP Web开发',
                'content' => '已完成资源上传、文件类型校验和下载测试，提交说明中记录了本班共享资源的权限验证过程。',
                'status' => 'submitted',
            ]
        );

        HomeworkSubmission::firstOrCreate(
            ['user_id' => $student2->id, 'assignment_title' => '数据库课程设计表结构检查'],
            [
                'teacher_id' => $teacher2->id,
                'course_name' => '数据库原理',
                'content' => '已按照检查表补充主键、外键和索引说明，提交前还需要老师确认字段命名是否规范。',
                'status' => 'submitted',
            ]
        );
    }

    private function ensureSampleResourceFiles(array $resourceRows): void
    {
        foreach ($resourceRows as $row) {
            $samplePath = database_path('seeders/sample_files/' . basename($row['file_path']));
            if (!is_file($samplePath)) {
                $samplePath = $this->sampleTemplatePath($row['file_ext']);
            }

            if (!is_file($samplePath)) {
                continue;
            }

            $content = file_get_contents($samplePath);
            Storage::disk('public')->put($row['file_path'], $content);

            Resource::where('file_path', $row['file_path'])->update([
                'file_size' => strlen($content),
            ]);
        }
    }

    private function sampleTemplatePath(?string $extension): string
    {
        return database_path('seeders/sample_files/' . match (strtolower((string) $extension)) {
            'ppt', 'pptx' => 'php-web-preview.pptx',
            'pdf' => 'mysql-design.pdf',
            'doc', 'docx' => 'report-template.docx',
            'zip', 'rar', '7z' => 'data-structure-cases.zip',
            'mp3', 'wav', 'aac' => 'cet-listening.mp3',
            'mp4', 'mov', 'avi' => 'web-deploy.mp4',
            default => 'report-template.docx',
        });
    }
}

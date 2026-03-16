# 佛系消消消解密 N-Queens

N-Queens 求解器，通过图片识别并求解游戏中的棋盘谜题。

## 环境要求

- PHP 8.1+
- GD 扩展（图片处理）

## 目录结构

```
N-Queens/
├── index.html    # 前端页面
├── solve.php     # 后端求解器
└── DEPLOY.md    # 部署文档
```

## 部署步骤

### 1. 上传文件

将 `index.html` 和 `solve.php` 上传到网站根目录。

### 2. 配置 PHP

推荐 php.ini 配置：

```ini
; 最大执行时间 (秒)
max_execution_time = 60

; 内存限制
memory_limit = 128M

; POST 最大尺寸
post_max_size = 10M

; 上传文件最大尺寸
upload_max_filesize = 10M
```

### 3. 验证 GD 扩展

```bash
php -m | grep gd
```

如果没有，安装：

```bash
# Ubuntu/Debian
apt install php8.1-gd

# CentOS
yum install php81-php-gd
```

### 4. 权限设置

```bash
chmod 755 /var/www/html
chown www-data:www-data /var/www/html
```

## 使用说明

1. 点击"选择图片"上传游戏截图
2. 拖拽框选游戏棋盘区域
3. 输入网格 N 值（5-30）
4. 点击"确认裁剪并求解"
5. 查看红色圆圈标记的答案

## 故障排查

### 问题：图片接收失败

- 检查 `upload_max_filesize` 配置
- 检查文件夹权限

### 问题：无解

- 确认 N 值填写正确
- 确保截图清晰，棋盘完整

### 问题：求解超时

- 增加 `max_execution_time`
- 降低图片尺寸（前端的已处理为 600x600）

## 技术栈

| 组件 | 说明 |
|------|------|
| 前端 | Cropper.js 图片裁剪 |
| 后端 | PHP 8.1 + GD 库 |
| 算法 | N-Queens 回溯算法 |

## 许可证

MIT

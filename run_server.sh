#!/bin/bash
echo "Starting Laravel Dev Server with 256MB Upload Limits..."
php -d upload_max_filesize=256M -d post_max_size=256M -d memory_limit=1024M -d max_execution_time=3600 artisan serve "$@"

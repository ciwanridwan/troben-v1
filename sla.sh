#!/bin/bash
now=$(date +'%Y-%m-%d %H:%M:%S')
echo "Begin fetch sla at : " $now

cd /opt/tb-core
/usr/local/bin/docker-compose exec -T core php artisan core:sla

now=$(date +'%Y-%m-%d %H:%M:%S')
echo "End sla at : " $now

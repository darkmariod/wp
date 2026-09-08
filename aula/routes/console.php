<?php

use App\Console\Commands\BackupDatabase;
use App\Console\Commands\PublishScheduledContent;
use Illuminate\Support\Facades\Schedule;

Schedule::command(PublishScheduledContent::class)->everyMinute();

Schedule::command(BackupDatabase::class)->daily();

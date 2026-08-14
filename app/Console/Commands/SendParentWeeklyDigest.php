<?php

namespace App\Console\Commands;

use App\Mail\ParentWeeklyDigestMail;
use App\Models\User;
use App\Services\Mail\MailGate;
use Illuminate\Console\Command;

/**
 * الملخّص الأسبوعيّ لأولياء الأمور عن نشاط أبنائهم (خطّة أدوار البريد P8).
 * يُرسَل فقط لمن كان لأحد أبنائه نشاطٌ هذا الأسبوع. عبر MailGate (فئة digest).
 */
class SendParentWeeklyDigest extends Command
{
    protected $signature = 'emails:digest-parent-weekly';

    protected $description = 'إرسال الملخّص الأسبوعيّ لأولياء الأمور عن أبنائهم';

    public function handle(): int
    {
        $sent = 0;

        User::where('role', 'parent')->whereNotNull('email')->where('email', '!=', '')
            ->with('children:id,name')->chunkById(200, function ($parents) use (&$sent) {
                foreach ($parents as $parent) {
                    if ($parent->children->isEmpty()) {
                        continue;
                    }

                    $summary = [];
                    $total = 0;
                    foreach ($parent->children as $child) {
                        $pts = 0;
                        try {
                            $pts = (int) $child->points()->where('created_at', '>=', now()->subDays(7))->sum('points');
                        } catch (\Throwable $e) {
                            // علاقة غير متاحة — تُخطّى
                        }
                        $summary[] = ['name' => $child->name, 'points' => $pts];
                        $total += $pts;
                    }

                    if ($total <= 0) {
                        continue; // لا نشاط لأيّ ابن هذا الأسبوع
                    }

                    if (MailGate::send($parent, 'parent_weekly_digest', 'digest', new ParentWeeklyDigestMail($parent, $summary))) {
                        $sent++;
                    }
                }
            });

        $this->info("الملخّص الأسبوعيّ لأولياء الأمور: أُرسِل لـ{$sent} وليّ أمر.");

        return self::SUCCESS;
    }
}

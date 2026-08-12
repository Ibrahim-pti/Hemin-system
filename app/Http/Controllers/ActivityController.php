<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    /** ناوی کوردی مۆدێلەکان — بۆ پیشاندان لە مێژوودا. */
    public const SUBJECTS = [
        'App\Models\Item' => 'کاڵا',
        'App\Models\Order' => 'وەسڵ',
        'App\Models\Purchase' => 'پسوولەی کڕین',
        'App\Models\Payment' => 'حەقدی',
        'App\Models\Customer' => 'کڕیار',
        'App\Models\Supplier' => 'فرۆشیار',
        'App\Models\Employee' => 'کارمەند',
        'App\Models\ExternalJob' => 'ئیشی خاریجی',
        'App\Models\StockMovement' => 'جوڵەی مەخزەن',
        'App\Models\StockCount' => 'جەرد',
        'App\Models\Warehouse' => 'کۆگا',
        'App\Models\Attendance' => 'ئامادەبوون',
    ];

    public const EVENTS = [
        'created' => 'دروستکرا',
        'updated' => 'گۆڕدرا',
        'deleted' => 'سڕدرایەوە',
        'restored' => 'گەڕێنرایەوە',
    ];

    public function index(Request $request): View
    {
        $activities = Activity::query()
            ->with('causer')
            ->when($request->string('subject')->toString(), fn ($q, $s) => $q->where('subject_type', $s))
            ->when($request->string('event')->toString(), fn ($q, $e) => $q->where('event', $e))
            ->when($request->integer('user'), fn ($q, $id) => $q->where('causer_id', $id))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('activity.index', [
            'activities' => $activities,
            'subjects' => self::SUBJECTS,
            'events' => self::EVENTS,
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}

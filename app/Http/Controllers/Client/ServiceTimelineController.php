<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Services\ServiceTimelineService;

class ServiceTimelineController extends Controller
{
    private ServiceTimelineService $timelineService;

    public function __construct(ServiceTimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $timeline = $this->timelineService->getOrderTimeline($order);

        return response()->json([
            'success' => true,
            'timeline' => $timeline->toArray(),
        ]);
    }
}

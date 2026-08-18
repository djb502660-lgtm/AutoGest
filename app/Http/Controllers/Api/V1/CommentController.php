<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\AuthorizesOrders;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use AuthorizesOrders;

    public function store(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($denied = $this->denyUnlessCanUpdate($request->user(), $order)) {
            return $denied;
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $comment = OrderComment::create([
            'service_order_id' => $order->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        ActivityLog::record(
            'order.comment_added',
            "Observación técnica en orden {$order->order_number}.",
            $order,
        );

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                ],
                'created_at' => $comment->created_at?->format('Y-m-d H:i'),
            ],
        ], 201);
    }
}

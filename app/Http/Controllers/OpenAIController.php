<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatHistory;
use OpenAI;

class OpenAIController extends Controller
{
    public function chat(Request $request)
    {
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        // 獲取 session_id，沒有則生成新的
        $sessionId = $request->session()->get('chat_session_id', uniqid());
        $request->session()->put('chat_session_id', $sessionId);

        // 加載對話歷史
        $chatHistory = ChatHistory::where('session_id', $sessionId)->get(['role', 'message'])->toArray();

        // 過濾掉空的 `message` 並記錄問題
        $chatHistory = array_filter($chatHistory, function ($message) {
            return isset($message['message']) && trim($message['message']) !== '';
        });

        // 格式化歷史數據為 OpenAI API 要求的格式
        $formattedHistory = array_map(function ($message) {
            return [
                'role' => $message['role'],
                'content' => $message['message'],
            ];
        }, $chatHistory);

        // 添加當前用戶的消息到歷史記錄
        $userMessage = $request->input('message');
        if (empty($userMessage)) {
            return response()->json(['error' => 'Message cannot be empty'], 400);
        }

        $formattedHistory[] = ['role' => 'user', 'content' => $userMessage];

        // 發送完整歷史到 OpenAI API
        try {
            $response = $client->chat()->create([
                'model' => $request->input('model', 'gpt-3.5-turbo'), // 默認 gpt-3.5-turbo
                'messages' => array_merge(
                    [['role' => 'system', 'content' => 'You are a helpful assistant.']],
                    $formattedHistory
                ),
            ]);

            $botReply = $response['choices'][0]['message']['content'];

            // 儲存用戶消息和 ChatGPT 的回答到資料表
            ChatHistory::create([
                'session_id' => $sessionId,
                'role' => 'user',
                'message' => $userMessage,
            ]);
            ChatHistory::create([
                'session_id' => $sessionId,
                'role' => 'assistant',
                'message' => $botReply,
            ]);

            return response()->json([
                'message' => $botReply,
                'chat_history' => $formattedHistory,
            ]);
        } catch (\Exception $e) {
            \Log::error('OpenAI API Error: ' . $e->getMessage(), [
                'session_id' => $sessionId,
                'messages' => $formattedHistory,
            ]);

            return response()->json(['error' => 'Failed to connect to OpenAI API'], 500);
        }
    }

    public function getChatHistory(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');

        if (!$sessionId) {
            return response()->json(['history' => []]);
        }

        // 從資料庫中獲取聊天記錄
        $chatHistory = ChatHistory::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc') // 按時間順序排序
            ->get(['role', 'message']);   // 僅選取需要的欄位

        return response()->json(['history' => $chatHistory]);
    }

}

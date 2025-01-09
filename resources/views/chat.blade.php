<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ChatGPT 即時問答</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .user-message {
            background-color: #007bff;
            color: white;
            border-radius: 10px;
            padding: 8px 12px;
            display: inline-block;
            max-width: 80%;
            word-wrap: break-word;
            white-space: pre-wrap; /* 支持換行和空格 */
        }

        .bot-message {
            background-color: #6c757d;
            color: white;
            border-radius: 10px;
            padding: 8px 12px;
            display: inline-block;
            max-width: 80%;
            word-wrap: break-word;
            white-space: pre-wrap; /* 支持換行和空格 */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">ChatGPT 即時問答</h1>
        <div class="card mt-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="model-select" class="form-label">選擇模型：</label>
                    <select id="model-select" class="form-select">
                        <option value="gpt-3.5-turbo" selected>GPT-3.5 Turbo</option>
                        <option value="gpt-4">GPT-4</option>
                    </select>
                </div>
                <div id="chat-box" class="mb-4" style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                    <!-- 聊天記錄 -->
                </div>
                <form id="chat-form" onsubmit="sendMessage(event)">
                    <div class="input-group">
                        <textarea id="user-input" class="form-control" placeholder="輸入你的問題..." rows="2" required></textarea>
                        <button type="submit" class="btn btn-primary">發送</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const userInput = document.getElementById('user-input');
        const modelSelect = document.getElementById('model-select');

        document.addEventListener('DOMContentLoaded', () => {
            // 獲取聊天歷史記錄
            axios.get('/chat/history')
                .then(response => {
                    const history = response.data.history;

                    if (history.length > 0) {
                        history.forEach(chat => {
                            appendMessage(chat.role === 'user' ? 'user' : 'bot', chat.message);
                        });
                    }
                })
                .catch(error => {
                    console.error('無法加載聊天記錄:', error);
                });
        });

        function sendMessage(event) {
            event.preventDefault();
            const message = userInput.value.trim();
            const model = modelSelect.value; // 獲取選擇的模型

            if (!message) {
                alert('請輸入有效的消息！');
                return;
            }

            // 顯示用戶消息
            appendMessage('user', message);
            userInput.value = '';

            // 顯示載入中
            appendMessage('bot', '載入中...', true);

            // 發送請求到後端
            axios.post('/chat', { message, model })
                .then(response => {
                    // 移除載入中
                    removeLoadingMessage();
                    // 顯示 ChatGPT 的回答
                    appendMessage('bot', response.data.message);
                })
                .catch(error => {
                    console.error(error);
                    removeLoadingMessage();
                    appendMessage('bot', '抱歉，無法處理您的請求。');
                });
        }


        function appendMessage(sender, message, isLoading = false) {
            const div = document.createElement('div');
            div.classList.add('mb-2', sender === 'user' ? 'text-end' : 'text-start');
            const span = document.createElement('span');
            span.className = sender === 'user' ? 'user-message' : 'bot-message';
            span.style.whiteSpace = 'pre-wrap'; // 保留空格和換行
            span.textContent = message;
            div.appendChild(span);
            chatBox.appendChild(div);

            // 如果是「載入中」，添加唯一的 id
            if (isLoading) {
                span.id = 'loading-message';
            }

            div.appendChild(span);
            chatBox.appendChild(div);

            // 確保新消息可見
            div.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }


        function removeLoadingMessage() {
            const loadingMessage = document.getElementById('loading-message');
            if (loadingMessage) {
                loadingMessage.parentElement.remove();
            }
        }
        

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>

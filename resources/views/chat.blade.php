<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ChatGPT 即時問答</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">
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
        
        /* 卡片樣式 */
        .code-card {
            background-color: #2d2d2d; /* 深色背景 */
            color: #ffffff; /* 字體顏色 */
            border-radius: 8px; /* 卡片圓角 */
            padding: 35px 0 0; /* 上 35px，左右 0 下0*/
            margin-top: 10px; /* 卡片之間的垂直間距 */
            position: relative; /* 為了讓按鈕定位 */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3); /* 更柔和的陰影 */
            font-family: Consolas, Monaco, "Andale Mono", "Ubuntu Mono", monospace;
        }


        /* 複製按鈕樣式 */
        .copy-btn {
            position: absolute;
            top: 3px;
            right: 10px;
            background-color: #2d2d2d; /* 按鈕背景色（與卡片協調） */
            color: white; /* 白色文字 */
            border: 1px solid #444; /* 輕微的邊框 */
            border-radius: 5px;
            padding: 5px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .copy-btn:hover {
            background-color: #575757; /* 按鈕懸停顏色 */
            box-shadow: 0px 2px 5px rgba(255, 255, 255, 0.2); /* 懸停陰影 */
        }

        /* <pre> 語法高亮 */
        pre {
            background-color: transparent; /* 透明以顯示卡片背景 */
            padding: 0;
            margin: 0;
            overflow: auto; /* 滾動條（如有必要） */
            font-size: 14px; /* 更一致的字體大小 */
        }

        /* 卡片頂部的語言標籤 */
        .language-label {
            position: absolute;
            top: 10px;
            left: 15px;
            font-size: 12px;
            color: #cccccc; /* 語言標籤的顏色 */
            text-transform: uppercase;
        }

        pre code.hljs{
            border-radius: 0 0 8px 8px; /* 只指定下方圓角 */
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
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

            // 分段處理文字與程式碼
            const parts = message.split(/```/); // 用反引號分割訊息
            parts.forEach((part, index) => {
                if (index % 2 === 1) {
                    // 偵測程式語言（如果有，例如 `php` 在 ```php 的地方）
                    const match = part.match(/^(\w+)\n/); // 檢測是否以語言開頭（如 php, html）
                    let language = 'plaintext';
                    if (match) {
                        language = match[1]; // 抓取語言名稱
                        part = part.substring(match[0].length); // 移除語言標籤行
                    }

                    // 卡片容器
                    const card = document.createElement('div');
                    card.classList.add('code-card');

                    // 語言標籤
                    const languageLabel = document.createElement('div');
                    languageLabel.classList.add('language-label');
                    languageLabel.textContent = language;

                    // 複製按鈕
                    const copyButton = document.createElement('button');
                    copyButton.classList.add('copy-btn');
                    copyButton.textContent = '複製';

                    // 程式碼區塊
                    const pre = document.createElement('pre');
                    const code = document.createElement('code');
                    code.classList.add(`language-${language}`);
                    code.textContent = part.trim();

                    pre.appendChild(code);
                    card.appendChild(languageLabel); // 加入語言標籤
                    card.appendChild(copyButton); // 加入複製按鈕
                    card.appendChild(pre); // 加入程式碼區塊
                    span.appendChild(card); // 卡片加入訊息框

                    // 語法高亮
                    hljs.highlightElement(code);

                    // 複製功能
                    copyButton.addEventListener('click', () => {
                        navigator.clipboard.writeText(part.trim());
                        copyButton.textContent = '已複製';
                        setTimeout(() => {
                            copyButton.textContent = '複製';
                        }, 2000);
                    });
                } else {
                    // 偶數索引，表示普通文字
                    const textNode = document.createTextNode(part);
                    span.appendChild(textNode);
                }
            });

            div.appendChild(span);
            chatBox.appendChild(div);

            // 如果是「載入中」，添加唯一的 id
            if (isLoading) {
                span.id = 'loading-message';
            }

            // 確保新消息可見
            div.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }


        // 偵測程式碼語言的函式 (簡單範例)
        function detectLanguage(code) {
            if (code.trim().startsWith('<')) {
                return 'html'; // 偵測 HTML
            } else if (code.includes('function') || code.includes('<?php')) {
                return 'php'; // 偵測 PHP
            }
            return 'plaintext'; // 預設為純文字
        }


        // 啟用所有語法高亮
        document.addEventListener('DOMContentLoaded', () => {
            hljs.highlightAll();
        });

        document.addEventListener('DOMContentLoaded', () => {
            // 初始化 clipboard.js
            new ClipboardJS('.copy-btn', {
                target: trigger => trigger.nextElementSibling // 複製與按鈕相鄰的 <pre>
            });
        });


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

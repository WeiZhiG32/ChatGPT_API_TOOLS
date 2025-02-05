## 關於這個專案

此專案是使用 Laravel 框架打造並提供與 OpenAI API 的接合功能，目前有模擬使用 ChatGPT 的功能，包括可以複製程式碼，後續會再擴充更多的功能

Laravel is a web application framework designed to simplify common development tasks, offering tools such as routing, database ORM, and real-time event broadcasting. It provides an enjoyable and efficient development experience.

---

## 設定說明

請按照下列步驟啟動：

1. **Clone the repository**

   ```bash
   git clone https://github.com/WeiZhiG32/ChatGPT_API_TOOLS.git
   cd your-project
   ```

   使用 git clone 指令將遠端存儲庫複製到本地，並透過 cd 進入專案目錄。請注意 your-project 需替換為實際目錄名稱。

2. **Copy the ****`.env`**** file and modify it**

   ```bash
   cp .env.example .env
   ```

   複製 .env.example 模板文件為新的 .env 檔案。此檔案用於設定環境變數，需根據您的 OpenAI API 金鑰、資料庫連線等配置進行修改。

3. **Install dependencies**

   ```bash
   composer install
   ```
   透過 PHP 的套件管理工具 Composer 安裝 Laravel 框架所需的所有依賴套件。

4. **Generate the application key**

   ```bash
   php artisan key:generate
   ```
   使用 Laravel 的 Artisan 命令行工具生成應用程式安全金鑰，此金鑰會自動寫入 .env 檔案。

5. **Run database migrations**

   ```bash
   php artisan migrate
   ```
   透過 Artisan 執行資料庫遷移 (Migrations)，為專案建立所需的資料庫表結構。

6. **Start the development server**

   ```bash
   php artisan serve
   ```
   使用 Artisan 內建的開發伺服器啟動應用程式，預設會運行在 http://localhost:8000

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


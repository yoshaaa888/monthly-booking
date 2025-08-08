# WordPress ページ作成マニュアル

## 📄 作成が必要なページ一覧

Monthly Booking プラグインのテストに必要なWordPressページを以下の手順で作成してください。

---

## 🏠 ページ1: 見積もりページ

### 基本情報
- **ページタイトル**: `月額宿泊 見積もり`
- **スラッグ**: `monthly-estimate`
- **ページタイプ**: 固定ページ

### 内容
```html
<h1>月額宿泊 見積もり</h1>
<p>お客様のご希望に合わせて、月額宿泊の料金を見積もりいたします。</p>
<p>下記フォームに必要事項をご入力ください。</p>

[monthly_booking_estimate]

<style>
.monthly-booking-estimate-form {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f9f9f9;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.options-section {
    border: 1px solid #ddd;
    padding: 15px;
    margin: 15px 0;
    border-radius: 4px;
    background: white;
}

.option-item {
    margin: 10px 0;
    padding: 8px;
    border: 1px solid #eee;
    border-radius: 4px;
}

.estimate-result {
    margin-top: 20px;
    padding: 20px;
    border: 2px solid #4CAF50;
    border-radius: 8px;
    background: #f0f8f0;
}

.cost-breakdown {
    margin: 15px 0;
}

.cost-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.cost-total {
    font-weight: bold;
    font-size: 1.2em;
    border-top: 2px solid #333;
    padding-top: 10px;
    margin-top: 10px;
}

.campaign-badge {
    background: #ff6b6b;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    margin-left: 5px;
}

.campaign-badge.early {
    background: #4ecdc4;
}

.campaign-badge.last_minute {
    background: #ff6b6b;
}

#submit-booking-btn {
    background: #4CAF50;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 4px;
    font-size: 1.1em;
    cursor: pointer;
    width: 100%;
    margin-top: 20px;
}

#submit-booking-btn:hover {
    background: #45a049;
}
</style>
```

---

## 📋 ページ2: カレンダーページ

### 基本情報
- **ページタイトル**: `予約カレンダー`
- **スラッグ**: `booking-calendar`
- **ページタイプ**: 固定ページ

### 内容
```html
<h1>予約カレンダー</h1>
<p>物件の空室状況をカレンダーでご確認いただけます。</p>

[monthly_booking_calendar]

<div class="calendar-legend">
    <h3>表示説明</h3>
    <ul>
        <li><span class="legend-available">〇</span> 予約可能</li>
        <li><span class="legend-unavailable">×</span> 予約不可</li>
        <li><span class="legend-campaign">△</span> キャンペーン対象</li>
    </ul>
</div>

<style>
.calendar-legend {
    margin: 20px 0;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}

.calendar-legend ul {
    list-style: none;
    padding: 0;
}

.calendar-legend li {
    margin: 5px 0;
    display: flex;
    align-items: center;
}

.legend-available,
.legend-unavailable,
.legend-campaign {
    display: inline-block;
    width: 20px;
    height: 20px;
    text-align: center;
    margin-right: 10px;
    border-radius: 50%;
    font-weight: bold;
}

.legend-available {
    background: #4CAF50;
    color: white;
}

.legend-unavailable {
    background: #f44336;
    color: white;
}

.legend-campaign {
    background: #ff9800;
    color: white;
}
</style>
```

---

## 🔍 ページ3: テスト結果確認ページ

### 基本情報
- **ページタイトル**: `テスト結果確認`
- **スラッグ**: `test-results`
- **ページタイプ**: 固定ページ

### 内容
```html
<h1>テスト結果確認</h1>
<p>予約データの確認とテスト結果を表示します。</p>

<div id="test-results-container">
    <h2>最新の予約データ</h2>
    <div id="recent-bookings">
        <p>データを読み込み中...</p>
    </div>
    
    <h2>データベース統計</h2>
    <div id="db-stats">
        <p>統計を読み込み中...</p>
    </div>
</div>

<script>
// 簡易的なデータ表示（管理者向け）
document.addEventListener('DOMContentLoaded', function() {
    // 実際の実装では AJAX でデータを取得
    const recentBookings = document.getElementById('recent-bookings');
    const dbStats = document.getElementById('db-stats');
    
    // サンプル表示
    recentBookings.innerHTML = `
        <table border="1" style="width:100%; border-collapse: collapse;">
            <tr>
                <th>予約ID</th>
                <th>顧客名</th>
                <th>物件</th>
                <th>チェックイン</th>
                <th>チェックアウト</th>
                <th>金額</th>
                <th>ステータス</th>
            </tr>
            <tr>
                <td colspan="7">実際のデータは管理画面の「Monthly Room Booking」メニューでご確認ください</td>
            </tr>
        </table>
    `;
    
    dbStats.innerHTML = `
        <ul>
            <li>登録物件数: 5件</li>
            <li>オプション数: 9件</li>
            <li>アクティブキャンペーン: 2件</li>
            <li>顧客数: 3件</li>
        </ul>
        <p><strong>詳細は WordPress管理画面 → Monthly Room Booking でご確認ください</strong></p>
    `;
});
</script>

<style>
#test-results-container {
    max-width: 1000px;
    margin: 20px auto;
}

#test-results-container h2 {
    border-bottom: 2px solid #333;
    padding-bottom: 10px;
}

#recent-bookings table {
    width: 100%;
    margin: 15px 0;
}

#recent-bookings th,
#recent-bookings td {
    padding: 8px;
    text-align: left;
}

#db-stats ul {
    list-style-type: disc;
    margin-left: 20px;
}

#db-stats li {
    margin: 5px 0;
}
</style>
```

---

## 📝 ページ作成手順

### 1. WordPress管理画面にログイン
1. ブラウザで `http://t-monthlycampaign.local/wp-admin/` にアクセス
2. 管理者アカウントでログイン

### 2. 固定ページの作成
1. 左メニューから「固定ページ」→「新規追加」をクリック
2. 上記の各ページ情報を入力
3. 「公開」ボタンをクリック

### 3. ページの確認
1. 各ページが正常に表示されることを確認
2. ショートコードが正しく動作することを確認

---

## ⚠️ 注意事項

- ショートコード `[monthly_booking_estimate]` と `[monthly_booking_calendar]` は、プラグインが有効化されている必要があります
- CSSスタイルは各ページに直接記述していますが、テーマのstyle.cssに移動することも可能です
- テスト結果確認ページは管理者向けの簡易表示です。実際のデータは管理画面で確認してください

---

## 🔗 作成後のURL

作成完了後、以下のURLでアクセスできます：

- 見積もりページ: `http://t-monthlycampaign.local/monthly-estimate/`
- カレンダーページ: `http://t-monthlycampaign.local/booking-calendar/`
- テスト結果確認: `http://t-monthlycampaign.local/test-results/`

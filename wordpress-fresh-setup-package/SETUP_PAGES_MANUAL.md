# 見積りページ・カレンダーページ作成マニュアル

## 📋 概要

Monthly Booking Plugin v2.0.0で使用する固定ページの作成手順を説明します。

## 🎯 作成するページ

1. **見積りページ** - 料金見積り機能
2. **カレンダーページ** - 予約状況確認機能
3. **管理ページ** - 予約管理機能（管理者用）

## 📄 1. 見積りページの作成

### 基本設定
- **ページタイトル**: `月額見積り` または `料金見積り`
- **パーマリンク**: `monthly-estimate` (推奨)
- **ページテンプレート**: デフォルト

### ページ内容

```html
<h2>🏠 月額マンスリー見積り</h2>
<p>ご希望の条件を入力して、料金を確認してください。</p>

[monthly_booking_estimate]

<div style="margin-top: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
<h3>📋 ご利用の流れ</h3>
<ol>
<li><strong>条件入力</strong> - 入居日・退去日・人数を選択</li>
<li><strong>見積り確認</strong> - 詳細な料金内訳を確認</li>
<li><strong>オプション選択</strong> - 必要なオプションを追加</li>
<li><strong>予約申し込み</strong> - 内容確認後にお申し込み</li>
</ol>
</div>

<div style="margin-top: 20px; padding: 15px; background-color: #e8f4fd; border-left: 4px solid #2196F3;">
<h4>💡 料金について</h4>
<ul>
<li><strong>非課税項目</strong>: 日額賃料・共益費</li>
<li><strong>課税項目</strong>: 清掃費・布団代・鍵交換代・オプション類</li>
<li><strong>割引制度</strong>: オプション2個以上で割引適用</li>
<li><strong>キャンペーン</strong>: 早割・即入居割を自動適用</li>
</ul>
</div>
```

## 📅 2. カレンダーページの作成

### 基本設定
- **ページタイトル**: `予約カレンダー` または `空室状況`
- **パーマリンク**: `monthly-calendar` (推奨)
- **ページテンプレート**: デフォルト

### ページ内容

```html
<h2>📅 予約カレンダー・空室状況</h2>
<p>各物件の予約状況をカレンダーで確認できます。</p>

[monthly_booking_calendar]

<div style="margin-top: 30px; padding: 20px; background-color: #f0f8f0; border-radius: 5px;">
<h3>📊 カレンダーの見方</h3>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
<div style="display: flex; align-items: center; gap: 5px;">
<span style="width: 20px; height: 20px; background-color: #4CAF50; border-radius: 3px; display: inline-block;"></span>
<span>〇 空室・予約可能</span>
</div>
<div style="display: flex; align-items: center; gap: 5px;">
<span style="width: 20px; height: 20px; background-color: #f44336; border-radius: 3px; display: inline-block;"></span>
<span>× 予約済み・利用不可</span>
</div>
<div style="display: flex; align-items: center; gap: 5px;">
<span style="width: 20px; height: 20px; background-color: #FF9800; border-radius: 3px; display: inline-block;"></span>
<span>△ キャンペーン対象日</span>
</div>
</div>
</div>

<div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
<h4>⚠️ 注意事項</h4>
<ul>
<li>カレンダーは180日先まで表示されます</li>
<li>予約には清掃期間（5日間）が含まれます</li>
<li>キャンペーン適用条件は自動判定されます</li>
</ul>
</div>
```

## 🔧 3. 管理ページの作成（管理者用）

### 基本設定
- **ページタイトル**: `予約管理` 
- **パーマリンク**: `booking-admin` (推奨)
- **ページテンプレート**: デフォルト
- **公開状態**: 非公開（管理者のみアクセス）

### ページ内容

```html
<h2>🔧 予約管理システム</h2>
<p>管理者用の予約管理機能です。</p>

[monthly_booking_admin]

<div style="margin-top: 30px;">
<h3>📋 管理機能一覧</h3>
<ul>
<li><strong>予約一覧</strong> - 全予約の確認・編集</li>
<li><strong>顧客管理</strong> - 顧客情報の管理</li>
<li><strong>物件管理</strong> - 物件・部屋情報の編集</li>
<li><strong>オプション管理</strong> - オプション設定</li>
<li><strong>キャンペーン管理</strong> - キャンペーン設定</li>
</ul>
</div>
```

## 🎨 4. CSSスタイリング（オプション）

### カスタムCSS追加

WordPress管理画面の **外観** → **カスタマイズ** → **追加CSS** に以下を追加：

```css
/* Monthly Booking Plugin スタイル */
.monthly-booking-form {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.monthly-booking-form .form-group {
    margin-bottom: 20px;
}

.monthly-booking-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.monthly-booking-form input,
.monthly-booking-form select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.monthly-booking-form button {
    background-color: #2196F3;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.monthly-booking-form button:hover {
    background-color: #1976D2;
}

.estimate-results {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #2196F3;
}

.cost-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.cost-total {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    font-size: 18px;
    font-weight: bold;
    border-top: 2px solid #2196F3;
    margin-top: 10px;
}

.tax-breakdown {
    background-color: #e8f4fd;
    padding: 10px;
    margin: 5px 0;
    border-radius: 4px;
}

.tax-detail {
    font-size: 14px;
    color: #666;
    padding-left: 20px;
}

.campaign-badge {
    background-color: #FF9800;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 10px;
}

.calendar-container {
    max-width: 1200px;
    margin: 0 auto;
}

.calendar-month {
    margin-bottom: 30px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.calendar-header {
    background-color: #2196F3;
    color: white;
    padding: 15px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #ddd;
}

.calendar-day {
    background-color: white;
    padding: 10px;
    text-align: center;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-day.available {
    background-color: #e8f5e8;
    color: #2e7d32;
}

.calendar-day.booked {
    background-color: #ffebee;
    color: #c62828;
}

.calendar-day.campaign {
    background-color: #fff3e0;
    color: #ef6c00;
}

@media (max-width: 768px) {
    .monthly-booking-form {
        margin: 10px;
        padding: 15px;
    }
    
    .cost-item {
        flex-direction: column;
        gap: 5px;
    }
    
    .calendar-grid {
        font-size: 14px;
    }
}
```

## 🔗 5. メニューへの追加

### ナビゲーションメニューに追加

1. WordPress管理画面で **外観** → **メニュー**
2. 作成したページを選択して **メニューに追加**
3. メニュー項目の順序を調整：
   - 月額見積り
   - 予約カレンダー
   - （その他のページ）

### ウィジェットエリアに追加（オプション）

1. **外観** → **ウィジェット**
2. **カスタムHTML** ウィジェットを追加
3. 以下のHTMLを入力：

```html
<div style="background: #f0f8f0; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
<h4 style="margin-top: 0;">🏠 月額マンスリー</h4>
<p style="margin-bottom: 10px;">お得な月額料金でご利用いただけます</p>
<a href="/monthly-estimate/" style="display: inline-block; background: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 14px;">見積り計算</a>
<a href="/monthly-calendar/" style="display: inline-block; background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 14px; margin-left: 5px;">空室確認</a>
</div>
```

## ✅ 6. 動作確認チェックリスト

### 見積りページ
- [ ] ページが正常に表示される
- [ ] フォームが動作する
- [ ] 見積り計算が正確
- [ ] 税区分表示が正しい
- [ ] オプション割引が適用される
- [ ] レスポンシブデザインが機能する

### カレンダーページ
- [ ] カレンダーが表示される
- [ ] 予約状況が正確
- [ ] キャンペーン表示が正しい
- [ ] 物件切り替えが動作する

### 管理ページ
- [ ] 管理者のみアクセス可能
- [ ] 予約一覧が表示される
- [ ] 編集機能が動作する

## 🚨 トラブルシューティング

### ショートコードが表示されない
1. プラグインが有効化されているか確認
2. ページを再読み込み
3. キャッシュをクリア

### スタイルが適用されない
1. テーマとの競合を確認
2. CSSの記述ミスを確認
3. ブラウザキャッシュをクリア

### フォームが動作しない
1. JavaScriptエラーを確認
2. 他のプラグインとの競合を確認
3. データベース接続を確認

このマニュアルに従って設定することで、Monthly Booking Plugin v2.0.0の見積り・カレンダー機能を完全にご利用いただけます。

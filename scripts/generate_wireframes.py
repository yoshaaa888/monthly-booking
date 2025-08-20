import os
from PIL import Image, ImageDraw, ImageFont

PAGES = [
    ("dashboard", "ダッシュボード", [
        "アラートカード（空室長期化/稼働低下/終了間近）",
        "KPIスナップ（稼働率/空室/予約/売上）",
        "最近の更新/操作履歴",
        "主導線A: アラート → カレンダー該当部屋 → [キャンペーン紐づけ]",
    ]),
    ("calendar", "カレンダー", [
        "6ヶ月グリッド（部屋×日付）",
        "レジェンド（空き/予約/キャンペーン）",
        "部屋詳細サイドパネル",
        "[キャンペーン紐づけ] ボタン",
    ]),
    ("rooms", "部屋一覧", [
        "テーブル（部屋/物件/稼働/キャンペーン有無）",
        "フィルタ＆複数選択",
        "主導線B: 複数選択 → [キャンペーン一括紐づけ]",
    ]),
    ("campaigns", "キャンペーン一覧", [
        "テーブル（名称/割引/期間/状態/割当数）",
        "フィルタ（状態/期間/プラン）",
        "主導線C: 行アクション [部屋へ紐づけ]",
    ]),
    ("reservations", "予約", [
        "テーブル（予約番号/部屋/顧客/日付/金額/状態）",
        "詳細スライドパネル（閲覧中心）",
    ]),
    ("rates", "料金", [
        "レート一覧（SS/S/M/L, 期間, 金額）",
        "一括編集、インポート/エクスポート",
    ]),
    ("options", "オプション", [
        "オプション一覧（名称/料金/課税/バンドル）",
        "ルール編集（バンドル割引）",
    ]),
    ("settings", "設定", [
        "事業者設定/権限/通知/機能フラグ",
        "保存ボタン",
    ]),
]

def draw_wireframe(filename, title, bullets, size=(1280, 800)):
    os.makedirs(os.path.dirname(filename), exist_ok=True)
    img = Image.new("RGB", size, color=(245, 245, 245))
    d = ImageDraw.Draw(img)

    d.rectangle([0,0,240,size[1]], fill=(230,230,230), outline=(200,200,200))
    d.rectangle([240,0,size[0],64], fill=(235,235,235), outline=(200,200,200))
    d.rectangle([256,80,size[0]-24,size[1]-24], outline=(180,180,180), width=2)

    d.text((264, 88), f"{title}（ワイヤーフレーム）", fill=(30,30,30))

    y = 130
    for b in bullets:
        d.ellipse((264, y+6, 274, y+16), fill=(120,120,120))
        d.text((284, y), b, fill=(40,40,40))
        y += 30

    menu_items = ["ダッシュボード","カレンダー","部屋一覧","キャンペーン","予約","料金","オプション","設定"]
    sy = 90
    for m in menu_items:
        d.rectangle([16, sy-6, 224, sy+20], outline=(210,210,210))
        d.text((28, sy), m, fill=(50,50,50))
        sy += 36

    img.save(filename, "PNG")

def main():
    base = os.path.join("docs","ui","wireframes")
    os.makedirs(base, exist_ok=True)
    for slug, title, bullets in PAGES:
        out = os.path.join(base, f"{slug}.png")
        draw_wireframe(out, title, bullets)

if __name__ == "__main__":
    main()

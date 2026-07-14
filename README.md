# PY_Tetris

以 Python + pygame 實作的俄羅斯方塊，場地為 `10 x 20`，支援七種基礎方塊、指定配色、消行計分、等級提升與 Game Over 規則。

## Install

```bash
pip install pygame
```

## Run

```bash
python tetris.py
```

## html_Tetris (HTML/JS/PHP)

```bash
php -S localhost:8000
```

然後開啟 `http://localhost:8000/index.php`。

## Controls

- 左移：`A` 或 `←`
- 右移：`D` 或 `→`
- 順時針旋轉 90°：`W` 或 `↑`
- 緩降（按住加速）：`S` 或 `↓`
- 瞬降並鎖定：`Space` 或 `Enter`
- 離開：`ESC`

## Scoring

- Single（1 行）：`100 x Level`
- Double（2 行）：`300 x Level`
- Triple（3 行）：`500 x Level`
- Tetris（4 行）：`800 x Level`

`Level` 初始為 1，每累計消除 10 行 +1。
# Tetris (COBOL)

以 GnuCOBOL 實作的終端機俄羅斯方塊，場地為 `10 x 20`，支援七種基礎方塊與指定配色、消行計分、等級提升與 Game Over 規則。

## Build

```bash
cobc -x -free -Wall -o tetris tetris.cob
```

## Run

```bash
./tetris
```

## Controls

- 左移：`A` 或 `LEFT`
- 右移：`D` 或 `RIGHT`
- 旋轉（順時針 90°）：`W` 或 `UP`
- 緩降：`S` 或 `DOWN`
- 瞬降並鎖定：`Space`（輸入空白）或直接按 `Enter`
- 離開：`Q`

## Scoring

- Single（1 行）：`100 x Level`
- Double（2 行）：`300 x Level`
- Triple（3 行）：`500 x Level`
- Tetris（4 行）：`800 x Level`

`Level` 初始為 1，每累計消除 10 行 +1。
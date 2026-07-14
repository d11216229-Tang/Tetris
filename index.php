<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>html_Tetris</title>
    <style>
        :root {
            --board-width: 10;
            --board-height: 20;
            --cell-size: 30px;
            --panel-width: 160px;
            --margin: 20px;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #111;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        #game {
            border: 1px solid #444;
            background: #000;
        }
    </style>
</head>
<body>
<canvas id="game"></canvas>
<script>
(() => {
    const BOARD_WIDTH = 10;
    const BOARD_HEIGHT = 20;
    const CELL_SIZE = 30;
    const LEFT_PANEL_WIDTH = 160;
    const MARGIN = 20;

    const SCREEN_WIDTH = LEFT_PANEL_WIDTH + BOARD_WIDTH * CELL_SIZE + MARGIN * 2;
    const SCREEN_HEIGHT = BOARD_HEIGHT * CELL_SIZE + MARGIN * 2;

    const COLORS = {
        I: '#00FFFF',
        O: '#FFFF00',
        T: '#A020F0',
        J: '#FFA500',
        L: '#0000FF',
        S: '#00C800',
        Z: '#DC0000'
    };

    const BASE_SCORES = {1: 100, 2: 300, 3: 500, 4: 800};
    const SHAPE_KEYS = ['I', 'O', 'T', 'J', 'L', 'S', 'Z'];
    const SHAPES = {
        I: [
            [[0, 1], [1, 1], [2, 1], [3, 1]],
            [[2, 0], [2, 1], [2, 2], [2, 3]],
            [[0, 2], [1, 2], [2, 2], [3, 2]],
            [[1, 0], [1, 1], [1, 2], [1, 3]]
        ],
        O: [
            [[1, 0], [2, 0], [1, 1], [2, 1]],
            [[1, 0], [2, 0], [1, 1], [2, 1]],
            [[1, 0], [2, 0], [1, 1], [2, 1]],
            [[1, 0], [2, 0], [1, 1], [2, 1]]
        ],
        T: [
            [[1, 0], [0, 1], [1, 1], [2, 1]],
            [[1, 0], [1, 1], [2, 1], [1, 2]],
            [[0, 1], [1, 1], [2, 1], [1, 2]],
            [[1, 0], [0, 1], [1, 1], [1, 2]]
        ],
        J: [
            [[0, 0], [0, 1], [1, 1], [2, 1]],
            [[1, 0], [2, 0], [1, 1], [1, 2]],
            [[0, 1], [1, 1], [2, 1], [2, 2]],
            [[1, 0], [1, 1], [0, 2], [1, 2]]
        ],
        L: [
            [[2, 0], [0, 1], [1, 1], [2, 1]],
            [[1, 0], [1, 1], [1, 2], [2, 2]],
            [[0, 1], [1, 1], [2, 1], [0, 2]],
            [[0, 0], [1, 0], [1, 1], [1, 2]]
        ],
        S: [
            [[1, 0], [2, 0], [0, 1], [1, 1]],
            [[1, 0], [1, 1], [2, 1], [2, 2]],
            [[1, 1], [2, 1], [0, 2], [1, 2]],
            [[0, 0], [0, 1], [1, 1], [1, 2]]
        ],
        Z: [
            [[0, 0], [1, 0], [1, 1], [2, 1]],
            [[2, 0], [1, 1], [2, 1], [1, 2]],
            [[0, 1], [1, 1], [1, 2], [2, 2]],
            [[1, 0], [0, 1], [1, 1], [0, 2]]
        ]
    };

    const canvas = document.getElementById('game');
    canvas.width = SCREEN_WIDTH;
    canvas.height = SCREEN_HEIGHT;
    const ctx = canvas.getContext('2d');

    const game = {
        board: Array.from({length: BOARD_HEIGHT}, () => Array(BOARD_WIDTH).fill(null)),
        score: 0,
        totalLines: 0,
        level: 1,
        current: null,
        gameOver: false,
        softDrop: false
    };

    function randomPiece() {
        const kind = SHAPE_KEYS[Math.floor(Math.random() * SHAPE_KEYS.length)];
        return {kind, x: Math.floor(BOARD_WIDTH / 2) - 2, y: -2, rotation: 0};
    }

    function blocks(piece) {
        return SHAPES[piece.kind][piece.rotation].map(([x, y]) => [piece.x + x, piece.y + y]);
    }

    function collides(piece) {
        return blocks(piece).some(([x, y]) => {
            if (x < 0 || x >= BOARD_WIDTH || y >= BOARD_HEIGHT) return true;
            if (y >= 0 && game.board[y][x] !== null) return true;
            return false;
        });
    }

    function spawnPiece() {
        game.current = randomPiece();
        if (collides(game.current)) game.gameOver = true;
    }

    function move(dx, dy) {
        const next = {...game.current, x: game.current.x + dx, y: game.current.y + dy};
        if (collides(next)) return false;
        game.current = next;
        return true;
    }

    function rotateCW() {
        const next = {...game.current, rotation: (game.current.rotation + 1) % 4};
        if (!collides(next)) game.current = next;
    }

    function clearLines() {
        const remaining = game.board.filter((row) => row.some((cell) => cell === null));
        const cleared = BOARD_HEIGHT - remaining.length;
        while (remaining.length < BOARD_HEIGHT) remaining.unshift(Array(BOARD_WIDTH).fill(null));
        game.board = remaining;
        return cleared;
    }

    function lockPiece() {
        for (const [x, y] of blocks(game.current)) {
            if (y >= 0) game.board[y][x] = COLORS[game.current.kind];
        }

        const cleared = clearLines();
        if (cleared > 0) {
            game.totalLines += cleared;
            game.level = Math.floor(game.totalLines / 10) + 1;
            game.score += BASE_SCORES[cleared] * game.level;
        }

        spawnPiece();
    }

    function hardDrop() {
        while (move(0, 1)) {}
        lockPiece();
    }

    function gravityIntervalMs() {
        if (game.softDrop) return 50;
        return Math.max(100, 700 - (game.level - 1) * 60);
    }

    function drawCell(x, y, color) {
        const px = LEFT_PANEL_WIDTH + MARGIN + x * CELL_SIZE;
        const py = MARGIN + y * CELL_SIZE;
        ctx.fillStyle = color || '#000000';
        ctx.fillRect(px, py, CELL_SIZE, CELL_SIZE);
        ctx.strokeStyle = '#5A5A5A';
        ctx.strokeRect(px, py, CELL_SIZE, CELL_SIZE);
    }

    function render() {
        ctx.fillStyle = '#000000';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#FFFFFF';
        ctx.font = '28px Arial';
        ctx.fillText(`Score: ${game.score}`, 20, 40);
        ctx.font = '22px Arial';
        ctx.fillText(`Lines: ${game.totalLines}`, 20, 75);
        ctx.fillText(`Level: ${game.level}`, 20, 105);

        for (let y = 0; y < BOARD_HEIGHT; y += 1) {
            for (let x = 0; x < BOARD_WIDTH; x += 1) {
                drawCell(x, y, game.board[y][x]);
            }
        }

        if (game.current) {
            for (const [x, y] of blocks(game.current)) {
                if (y >= 0) drawCell(x, y, COLORS[game.current.kind]);
            }
        }

        if (game.gameOver) {
            ctx.fillStyle = '#FF5050';
            ctx.font = '36px Arial';
            ctx.fillText('GAME OVER', LEFT_PANEL_WIDTH + 35, SCREEN_HEIGHT / 2 - 20);
        }
    }

    document.addEventListener('keydown', (event) => {
        if (game.gameOver) return;
        const key = event.key;
        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', ' '].includes(key) || key === 'Enter') {
            event.preventDefault();
        }

        if (key === 'a' || key === 'A' || key === 'ArrowLeft') move(-1, 0);
        else if (key === 'd' || key === 'D' || key === 'ArrowRight') move(1, 0);
        else if (key === 'w' || key === 'W' || key === 'ArrowUp') rotateCW();
        else if (key === 's' || key === 'S' || key === 'ArrowDown') game.softDrop = true;
        else if (key === ' ' || key === 'Enter') hardDrop();
    });

    document.addEventListener('keyup', (event) => {
        const key = event.key;
        if (key === 's' || key === 'S' || key === 'ArrowDown') game.softDrop = false;
    });

    spawnPiece();
    let lastTime = performance.now();
    let fallTimer = 0;

    function loop(now) {
        const dt = now - lastTime;
        lastTime = now;

        if (!game.gameOver) {
            fallTimer += dt;
            const interval = gravityIntervalMs();
            while (fallTimer >= interval) {
                fallTimer -= interval;
                if (!move(0, 1)) {
                    lockPiece();
                    break;
                }
            }
        }

        render();
        requestAnimationFrame(loop);
    }

    requestAnimationFrame(loop);
})();
</script>
</body>
</html>

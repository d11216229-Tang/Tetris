import random
import sys

import pygame

BOARD_WIDTH = 10
BOARD_HEIGHT = 20
CELL_SIZE = 30
LEFT_PANEL_WIDTH = 160
MARGIN = 20
SCREEN_WIDTH = LEFT_PANEL_WIDTH + BOARD_WIDTH * CELL_SIZE + MARGIN * 2
SCREEN_HEIGHT = BOARD_HEIGHT * CELL_SIZE + MARGIN * 2

BLACK = (0, 0, 0)
GRAY = (90, 90, 90)
WHITE = (240, 240, 240)

COLORS = {
    "I": (0, 255, 255),
    "O": (255, 255, 0),
    "T": (160, 32, 240),
    "J": (255, 165, 0),
    "L": (0, 0, 255),
    "S": (0, 200, 0),
    "Z": (220, 0, 0),
}

BASE_SCORES = {1: 100, 2: 300, 3: 500, 4: 800}

SHAPES = {
    "I": [
        [(0, 1), (1, 1), (2, 1), (3, 1)],
        [(2, 0), (2, 1), (2, 2), (2, 3)],
        [(0, 2), (1, 2), (2, 2), (3, 2)],
        [(1, 0), (1, 1), (1, 2), (1, 3)],
    ],
    "O": [
        [(1, 0), (2, 0), (1, 1), (2, 1)],
        [(1, 0), (2, 0), (1, 1), (2, 1)],
        [(1, 0), (2, 0), (1, 1), (2, 1)],
        [(1, 0), (2, 0), (1, 1), (2, 1)],
    ],
    "T": [
        [(1, 0), (0, 1), (1, 1), (2, 1)],
        [(1, 0), (1, 1), (2, 1), (1, 2)],
        [(0, 1), (1, 1), (2, 1), (1, 2)],
        [(1, 0), (0, 1), (1, 1), (1, 2)],
    ],
    "J": [
        [(0, 0), (0, 1), (1, 1), (2, 1)],
        [(1, 0), (2, 0), (1, 1), (1, 2)],
        [(0, 1), (1, 1), (2, 1), (2, 2)],
        [(1, 0), (1, 1), (0, 2), (1, 2)],
    ],
    "L": [
        [(2, 0), (0, 1), (1, 1), (2, 1)],
        [(1, 0), (1, 1), (1, 2), (2, 2)],
        [(0, 1), (1, 1), (2, 1), (0, 2)],
        [(0, 0), (1, 0), (1, 1), (1, 2)],
    ],
    "S": [
        [(1, 0), (2, 0), (0, 1), (1, 1)],
        [(1, 0), (1, 1), (2, 1), (2, 2)],
        [(1, 1), (2, 1), (0, 2), (1, 2)],
        [(0, 0), (0, 1), (1, 1), (1, 2)],
    ],
    "Z": [
        [(0, 0), (1, 0), (1, 1), (2, 1)],
        [(2, 0), (1, 1), (2, 1), (1, 2)],
        [(0, 1), (1, 1), (1, 2), (2, 2)],
        [(1, 0), (0, 1), (1, 1), (0, 2)],
    ],
}


class Tetris:
    def __init__(self) -> None:
        self.board = [[None for _ in range(BOARD_WIDTH)] for _ in range(BOARD_HEIGHT)]
        self.score = 0
        self.total_lines = 0
        self.level = 1
        self.current = self._new_piece()
        self.game_over = self._collides(self.current["x"], self.current["y"], self.current["rotation"])

    def _new_piece(self) -> dict:
        kind = random.choice(list(SHAPES.keys()))
        return {"kind": kind, "x": BOARD_WIDTH // 2 - 2, "y": -2, "rotation": 0}

    def _blocks(self, x: int, y: int, rotation: int, kind: str):
        for bx, by in SHAPES[kind][rotation]:
            yield x + bx, y + by

    def _collides(self, x: int, y: int, rotation: int) -> bool:
        for bx, by in self._blocks(x, y, rotation, self.current["kind"]):
            if bx < 0 or bx >= BOARD_WIDTH or by >= BOARD_HEIGHT:
                return True
            if by >= 0 and self.board[by][bx] is not None:
                return True
        return False

    def move(self, dx: int, dy: int) -> bool:
        nx = self.current["x"] + dx
        ny = self.current["y"] + dy
        if self._collides(nx, ny, self.current["rotation"]):
            return False
        self.current["x"] = nx
        self.current["y"] = ny
        return True

    def rotate_cw(self) -> None:
        nrot = (self.current["rotation"] + 1) % 4
        if not self._collides(self.current["x"], self.current["y"], nrot):
            self.current["rotation"] = nrot

    def hard_drop(self) -> None:
        while self.move(0, 1):
            pass
        self.lock_piece()

    def lock_piece(self) -> None:
        color = COLORS[self.current["kind"]]
        for bx, by in self._blocks(
            self.current["x"], self.current["y"], self.current["rotation"], self.current["kind"]
        ):
            if by >= 0:
                self.board[by][bx] = color

        cleared = self.clear_lines()
        if cleared:
            self.total_lines += cleared
            self.level = self.total_lines // 10 + 1
            self.score += BASE_SCORES[cleared] * self.level

        self.current = self._new_piece()
        if self._collides(self.current["x"], self.current["y"], self.current["rotation"]):
            self.game_over = True

    def clear_lines(self) -> int:
        remaining = [row for row in self.board if any(cell is None for cell in row)]
        cleared = BOARD_HEIGHT - len(remaining)
        while len(remaining) < BOARD_HEIGHT:
            remaining.insert(0, [None for _ in range(BOARD_WIDTH)])
        self.board = remaining
        return cleared

    def gravity_interval_ms(self, soft_drop: bool) -> int:
        if soft_drop:
            return 50
        return max(100, 700 - (self.level - 1) * 60)


def draw_cell(screen, x: int, y: int, color) -> None:
    px = LEFT_PANEL_WIDTH + MARGIN + x * CELL_SIZE
    py = MARGIN + y * CELL_SIZE
    rect = pygame.Rect(px, py, CELL_SIZE, CELL_SIZE)
    if color is not None:
        pygame.draw.rect(screen, color, rect)
    pygame.draw.rect(screen, GRAY, rect, 1)


def render(screen, font, small_font, game: Tetris) -> None:
    screen.fill(BLACK)

    score_text = font.render(f"Score: {game.score}", True, WHITE)
    lines_text = small_font.render(f"Lines: {game.total_lines}", True, WHITE)
    level_text = small_font.render(f"Level: {game.level}", True, WHITE)
    screen.blit(score_text, (20, 20))
    screen.blit(lines_text, (20, 60))
    screen.blit(level_text, (20, 90))

    for y in range(BOARD_HEIGHT):
        for x in range(BOARD_WIDTH):
            draw_cell(screen, x, y, game.board[y][x])

    kind = game.current["kind"]
    color = COLORS[kind]
    for bx, by in game._blocks(game.current["x"], game.current["y"], game.current["rotation"], kind):
        if by >= 0:
            draw_cell(screen, bx, by, color)

    if game.game_over:
        msg = font.render("GAME OVER", True, (255, 80, 80))
        hint = small_font.render("Press ESC or close window", True, WHITE)
        screen.blit(msg, (LEFT_PANEL_WIDTH + 35, SCREEN_HEIGHT // 2 - 30))
        screen.blit(hint, (LEFT_PANEL_WIDTH + 20, SCREEN_HEIGHT // 2 + 5))

    pygame.display.flip()


def main() -> None:
    pygame.init()
    pygame.display.set_caption("PY_Tetris")
    screen = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT))
    clock = pygame.time.Clock()
    font = pygame.font.SysFont(None, 34)
    small_font = pygame.font.SysFont(None, 28)

    game = Tetris()
    fall_timer = 0

    while True:
        dt = clock.tick(60)
        soft_drop = False

        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                pygame.quit()
                sys.exit(0)
            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_ESCAPE:
                    pygame.quit()
                    sys.exit(0)
                if game.game_over:
                    continue
                if event.key in (pygame.K_a, pygame.K_LEFT):
                    game.move(-1, 0)
                elif event.key in (pygame.K_d, pygame.K_RIGHT):
                    game.move(1, 0)
                elif event.key in (pygame.K_w, pygame.K_UP):
                    game.rotate_cw()
                elif event.key in (pygame.K_SPACE, pygame.K_RETURN, pygame.K_KP_ENTER):
                    game.hard_drop()
                    fall_timer = 0

        if not game.game_over:
            keys = pygame.key.get_pressed()
            soft_drop = keys[pygame.K_s] or keys[pygame.K_DOWN]
            fall_timer += dt
            interval = game.gravity_interval_ms(soft_drop)

            while fall_timer >= interval:
                fall_timer -= interval
                if not game.move(0, 1):
                    game.lock_piece()
                    break

        render(screen, font, small_font, game)


if __name__ == "__main__":
    main()

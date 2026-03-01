/**
 * Cart model (OOP). Represents shopping cart with lines and totals.
 * Synced with API via CartContext; this class holds state and calculations.
 */
export class Cart {
  constructor({ id = null, lines = [], total = 0 } = {}) {
    this.id = id;
    this.lines = Array.isArray(lines) ? lines : [];
    this.total = Number(total) || 0;
  }

  getItemCount() {
    return this.lines.reduce((acc, line) => acc + (line.quantity || 0), 0);
  }

  getTotal() {
    return this.lines.reduce((acc, line) => acc + (line.line_total || 0), 0);
  }

  hasItems() {
    return this.lines.length > 0;
  }

  findLineByProduct(productId) {
    return this.lines.find((l) => l.product_id === productId && !l.pack_id);
  }

  findLineByPack(packId) {
    return this.lines.find((l) => l.pack_id === packId);
  }

  static fromApi(data) {
    return new Cart({
      id: data?.id ?? null,
      lines: data?.lines ?? [],
      total: data?.total ?? 0,
    });
  }
}

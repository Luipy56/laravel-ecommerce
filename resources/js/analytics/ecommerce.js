import { pushDataLayer, pushEcommerceEvent } from './dataLayer';
import { hasAnalyticsConsent } from './consentStorage';

const CURRENCY = 'EUR';

function stripPii(value) {
  if (value == null) return undefined;
  const s = String(value).trim();
  if (!s) return undefined;
  // Never send emails / phones / obvious PII fragments
  if (s.includes('@') || /^\+?\d{7,}$/.test(s.replace(/[\s()-]/g, ''))) return undefined;
  return s.slice(0, 120);
}

/**
 * @param {Record<string, unknown>} raw
 * @returns {Record<string, unknown>|null}
 */
export function toGa4Item(raw) {
  if (!raw || typeof raw !== 'object') return null;
  const id = raw.item_id ?? raw.product_id ?? raw.pack_id ?? raw.id;
  if (id == null || id === '') return null;
  const name = stripPii(raw.item_name ?? raw.name ?? raw.product_name ?? raw.pack_name) || `item-${id}`;
  const item = {
    item_id: String(id),
    item_name: name,
  };
  const cat = stripPii(raw.item_category ?? raw.category_name ?? raw.category);
  if (cat) item.item_category = cat;
  const price = Number(raw.price ?? raw.unit_price ?? raw.line_unit_price);
  if (Number.isFinite(price)) item.price = Math.round(price * 100) / 100;
  const qty = Number(raw.quantity ?? 1);
  if (Number.isFinite(qty) && qty > 0) item.quantity = qty;
  return item;
}

/** @param {unknown[]} lines */
export function itemsFromCartLines(lines) {
  if (!Array.isArray(lines)) return [];
  return lines
    .map((l) =>
      toGa4Item({
        item_id: l.product_id ?? l.pack_id ?? l.id,
        item_name: l.product_name ?? l.pack_name ?? l.name,
        item_category: l.category_name,
        price: l.unit_price ?? l.price,
        quantity: l.quantity,
      }),
    )
    .filter(Boolean);
}

/** @param {unknown[]} lines */
export function itemsFromOrderLines(lines) {
  if (!Array.isArray(lines)) return [];
  return lines
    .map((l) =>
      toGa4Item({
        item_id: l.product_id ?? l.pack_id ?? l.id,
        item_name: l.product_name ?? l.pack_name ?? l.name,
        item_category: l.category_name,
        price: l.unit_price ?? (l.quantity ? Number(l.line_total) / Number(l.quantity) : l.line_total),
        quantity: l.quantity,
      }),
    )
    .filter(Boolean);
}

function guarded(fn) {
  try {
    if (!hasAnalyticsConsent()) return;
    fn();
  } catch {
    /* ignore */
  }
}

export function trackPageView(path, title) {
  guarded(() => {
    pushDataLayer({
      event: 'serra_page_view',
      page_path: path,
      page_title: title || undefined,
    });
  });
}

export function trackViewItemList(items, listId, listName) {
  guarded(() => {
    pushEcommerceEvent('view_item_list', {
      item_list_id: listId,
      item_list_name: listName,
      items: (items || []).map(toGa4Item).filter(Boolean),
    });
  });
}

export function trackSelectItem(item, listId, listName) {
  guarded(() => {
    const gaItem = toGa4Item(item);
    if (!gaItem) return;
    pushEcommerceEvent('select_item', {
      item_list_id: listId,
      item_list_name: listName,
      items: [gaItem],
    });
  });
}

export function trackViewItem(item) {
  guarded(() => {
    const gaItem = toGa4Item(item);
    if (!gaItem) return;
    pushEcommerceEvent('view_item', {
      currency: CURRENCY,
      value: gaItem.price ?? 0,
      items: [gaItem],
    });
  });
}

export function trackAddToCart(item, quantity = 1) {
  guarded(() => {
    const gaItem = toGa4Item({ ...item, quantity });
    if (!gaItem) return;
    pushEcommerceEvent('add_to_cart', {
      currency: CURRENCY,
      value: (gaItem.price ?? 0) * (gaItem.quantity ?? 1),
      items: [gaItem],
    });
  });
}

export function trackRemoveFromCart(item, quantity = 1) {
  guarded(() => {
    const gaItem = toGa4Item({ ...item, quantity });
    if (!gaItem) return;
    pushEcommerceEvent('remove_from_cart', {
      currency: CURRENCY,
      value: (gaItem.price ?? 0) * (gaItem.quantity ?? 1),
      items: [gaItem],
    });
  });
}

export function trackViewCart(items, value) {
  guarded(() => {
    pushEcommerceEvent('view_cart', {
      currency: CURRENCY,
      value: Number(value) || 0,
      items: itemsFromCartLines(items),
    });
  });
}

export function trackBeginCheckout(items, value) {
  guarded(() => {
    pushEcommerceEvent('begin_checkout', {
      currency: CURRENCY,
      value: Number(value) || 0,
      items: itemsFromCartLines(items),
    });
  });
}

export function trackAddShippingInfo(items, value) {
  guarded(() => {
    pushEcommerceEvent('add_shipping_info', {
      currency: CURRENCY,
      value: Number(value) || 0,
      items: itemsFromCartLines(items),
    });
  });
}

export function trackAddPaymentInfo(items, value, paymentType) {
  guarded(() => {
    pushEcommerceEvent('add_payment_info', {
      currency: CURRENCY,
      value: Number(value) || 0,
      payment_type: paymentType ? String(paymentType).slice(0, 40) : undefined,
      items: itemsFromCartLines(items),
    });
  });
}

/**
 * Fire once per paid order (sessionStorage dedupe).
 * @param {Record<string, unknown>} order
 */
export function trackPurchaseOnce(order) {
  guarded(() => {
    if (!order?.id || !order.has_payment) return;
    const key = `serra_ga4_purchase_${order.id}`;
    try {
      if (sessionStorage.getItem(key)) return;
    } catch {
      /* continue */
    }
    const items = itemsFromOrderLines(order.lines || []);
    const value = Number(order.grand_total ?? order.total ?? 0);
    const shipping = Number(order.shipping_cost ?? order.shipping_flat_eur ?? 0);
    pushEcommerceEvent('purchase', {
      transaction_id: String(order.id),
      currency: CURRENCY,
      value: Number.isFinite(value) ? value : 0,
      shipping: Number.isFinite(shipping) ? shipping : undefined,
      items,
    });
    try {
      sessionStorage.setItem(key, '1');
    } catch {
      /* ignore */
    }
  });
}

export function trackGenerateLead(formId) {
  guarded(() => {
    pushDataLayer({
      event: 'generate_lead',
      form_id: formId ? String(formId).slice(0, 60) : undefined,
    });
  });
}

export function trackViewSearchResults(searchTerm) {
  const term = stripPii(searchTerm);
  if (!term) return;
  guarded(() => {
    pushDataLayer({
      event: 'view_search_results',
      search_term: term,
    });
  });
}

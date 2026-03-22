/**
 * Product model (OOP). Represents a product for display and cart operations.
 */
export class Product {
  constructor({
    id,
    code,
    name,
    description,
    price,
    stock,
    weight_kg = null,
    is_double_clutch = false,
    has_card = false,
    security_level = null,
    competitor_url = null,
    category,
    images = [],
    features = [],
    variant_options = [],
    is_extra_keys_available = false,
    extra_key_unit_price = null,
  } = {}) {
    this.id = id;
    this.code = code;
    this.name = name;
    this.description = description;
    this.price = Number(price) || 0;
    this.stock = Number(stock) || 0;
    this.weight_kg = weight_kg != null && weight_kg !== '' ? Number(weight_kg) : null;
    this.is_double_clutch = Boolean(is_double_clutch);
    this.has_card = Boolean(has_card);
    this.security_level = security_level || null;
    this.competitor_url = competitor_url && String(competitor_url).trim() ? String(competitor_url).trim() : null;
    this.category = category;
    this.images = Array.isArray(images) ? images : [];
    this.features = Array.isArray(features) ? features : [];
    this.variant_options = Array.isArray(variant_options) ? variant_options : [];
    this.is_extra_keys_available = Boolean(is_extra_keys_available);
    this.extra_key_unit_price = extra_key_unit_price != null ? Number(extra_key_unit_price) : null;
  }

  /** Fallback image when product has no image or URL is empty. */
  static get fallbackImageUrl() {
    return '/images/dummy.jpg';
  }

  get primaryImageUrl() {
    const url = this.images?.[0]?.url;
    return (url && String(url).trim()) ? url : Product.fallbackImageUrl;
  }

  get formattedPrice() {
    return new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(this.price);
  }

  get formattedExtraKeyPrice() {
    if (this.extra_key_unit_price == null) return null;
    return new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(this.extra_key_unit_price);
  }

  /** All displayable image URLs in order (primary first). */
  get imageUrls() {
    const fromApi = this.images?.filter((img) => img?.url && String(img.url).trim()).map((img) => img.url) ?? [];
    return fromApi.length > 0 ? fromApi : [Product.fallbackImageUrl];
  }

  static fromApi(data) {
    return new Product({
      id: data?.id,
      code: data?.code,
      name: data?.name,
      description: data?.description,
      price: data?.price,
      stock: data?.stock,
      weight_kg: data?.weight_kg,
      is_double_clutch: data?.is_double_clutch,
      has_card: data?.has_card,
      security_level: data?.security_level,
      competitor_url: data?.competitor_url,
      category: data?.category,
      images: data?.images ?? [],
      features: data?.features ?? [],
      is_extra_keys_available: data?.is_extra_keys_available,
      extra_key_unit_price: data?.extra_key_unit_price,
      variant_options: data?.variant_options ?? [],
    });
  }
}

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
    category,
    images = [],
    features = [],
    variant_options = [],
    is_installable = false,
    installation_price = null,
    is_extra_keys_available = false,
    extra_key_unit_price = null,
  } = {}) {
    this.id = id;
    this.code = code;
    this.name = name;
    this.description = description;
    this.price = Number(price) || 0;
    this.stock = Number(stock) || 0;
    this.category = category;
    this.images = Array.isArray(images) ? images : [];
    this.features = Array.isArray(features) ? features : [];
    this.variant_options = Array.isArray(variant_options) ? variant_options : [];
    this.is_installable = Boolean(is_installable);
    this.installation_price = installation_price != null ? Number(installation_price) : null;
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

  get formattedInstallationPrice() {
    if (this.installation_price == null) return null;
    return new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(this.installation_price);
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
      category: data?.category,
      images: data?.images ?? [],
      features: data?.features ?? [],
      is_installable: data?.is_installable,
      installation_price: data?.installation_price,
      is_extra_keys_available: data?.is_extra_keys_available,
      extra_key_unit_price: data?.extra_key_unit_price,
      variant_options: data?.variant_options ?? [],
    });
  }
}

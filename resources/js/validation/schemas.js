import { z } from 'zod';

const PAYMENT_METHODS = z.enum(['card', 'paypal']);

/** Optional phone: empty or international-style with at least 6 digits. */
export const optionalPhoneString = z
  .string()
  .max(50)
  .transform((s) => s.trim())
  .refine(
    (s) => s === '' || (/^[\d+\s().-]+$/.test(s) && (s.match(/\d/g) || []).length >= 6),
    { message: 'validation.phone' }
  );

export const requiredEmail = z.string().trim().min(1, { message: 'validation.required' }).max(255).email();

export const optionalEmailString = z
  .string()
  .max(255)
  .transform((s) => s.trim())
  .refine((s) => s === '' || z.string().email().safeParse(s).success, { message: 'validation.email' });

export const postalCodeRequired = z.string().trim().min(1, { message: 'validation.required' }).max(20);

/** Shipping / address postal: same rules as backend max:20. */
export const postalCodeLoose = z
  .string()
  .trim()
  .min(1, { message: 'validation.required' })
  .max(20, { message: 'validation.max_length' });

export const loginSchema = z.object({
  login_email: requiredEmail,
  password: z.string().min(1, { message: 'validation.required' }),
});

export const adminLoginSchema = z.object({
  username: z.string().trim().min(1, { message: 'validation.required' }).max(255),
  password: z.string().min(1, { message: 'validation.required' }),
});

export const registerFormSchema = z
  .object({
    type: z.enum(['person', 'company']),
    identification: z.string().trim().max(20),
    login_email: requiredEmail,
    password: z.string().min(8, { message: 'validation.password_min' }).max(255),
    password_confirmation: z.string().min(1, { message: 'validation.required' }),
    name: z.string().trim().min(1, { message: 'validation.required' }).max(255),
    surname: z.string().trim().max(255),
    phone: optionalPhoneString,
    address_street: z.string().trim().max(255),
    address_city: z.string().trim().max(100),
    address_province: z.string().trim().max(100),
    address_postal_code: postalCodeRequired,
  })
  .refine((d) => d.password === d.password_confirmation, {
    path: ['password_confirmation'],
    message: 'validation.password_mismatch',
  });

export const customSolutionFormSchema = z.object({
  email: requiredEmail,
  phone: optionalPhoneString,
  problem_description: z.string().trim().min(1, { message: 'validation.required' }).max(5000),
  address_street: z.string().trim().max(255),
  address_city: z.string().trim().max(100),
  address_province: z.string().trim().max(100),
  address_postal_code: postalCodeRequired,
  address_note: z.string().trim().max(1000),
});

/**
 * @param {{ wantsInstallation: boolean, installationQuoteRequired: boolean }} opts
 */
export function checkoutFormSchema({ wantsInstallation, installationQuoteRequired }) {
  const shipping = {
    shipping_street: z.string().trim().min(1, { message: 'validation.required' }).max(255),
    shipping_city: z.string().trim().min(1, { message: 'validation.required' }).max(100),
    shipping_province: z.string().trim().max(100),
    shipping_postal_code: postalCodeLoose,
    shipping_note: z.string().max(5000),
  };

  const installationFields = {
    installation_street: z.string().trim().min(1, { message: 'validation.required' }).max(255),
    installation_city: z.string().trim().min(1, { message: 'validation.required' }).max(100),
    installation_postal_code: postalCodeLoose,
    installation_note: z.string().max(5000),
  };

  if (wantsInstallation && installationQuoteRequired) {
    return z.object({
      ...shipping,
      payment_method: z.string().optional().nullable(),
      ...installationFields,
    });
  }

  if (wantsInstallation) {
    return z.object({
      ...shipping,
      payment_method: PAYMENT_METHODS,
      ...installationFields,
    });
  }

  return z.object({
    ...shipping,
    payment_method: PAYMENT_METHODS,
    installation_street: z.string().max(255),
    installation_city: z.string().max(100),
    installation_postal_code: z.string().max(20),
    installation_note: z.string().max(5000),
  });
}

export const profileAccountSchema = z.object({
  identification: z.string().trim().max(20),
  name: z.string().trim().min(1, { message: 'validation.required' }).max(255),
  surname: z.string().trim().max(255),
  phone: optionalPhoneString,
});

export const profilePasswordSchema = z
  .object({
    password: z.string().min(8, { message: 'validation.password_min' }).max(255),
    password_confirmation: z.string().min(1, { message: 'validation.required' }),
  })
  .refine((d) => d.password === d.password_confirmation, {
    path: ['password_confirmation'],
    message: 'validation.password_mismatch',
  });

export const profileAddressSchema = z.object({
  type: z.enum(['shipping', 'installation', 'other']),
  label: z.string().trim().max(100),
  street: z.string().trim().min(1, { message: 'validation.required' }).max(255),
  city: z.string().trim().min(1, { message: 'validation.required' }).max(100),
  province: z.string().trim().max(100),
  postal_code: postalCodeLoose,
  is_primary: z.boolean(),
});

export const profileContactSchema = z.object({
  name: z.string().trim().min(1, { message: 'validation.required' }).max(255),
  surname: z.string().trim().max(255),
  phone: optionalPhoneString,
  phone2: optionalPhoneString,
  email: optionalEmailString,
  is_primary: z.boolean(),
});

const securityLevels = z.enum(['standard', 'high', 'very_high']);

/** Validates API payload built in AdminProductForm before submit. */
export const adminProductPayloadSchema = z.object({
  category_id: z.number().int().positive({ message: 'validation.invalid' }),
  variant_group_id: z.number().int().positive().nullable(),
  code: z.string().max(50).nullable(),
  name: z.string().trim().min(1, { message: 'validation.required' }).max(255),
  description: z.string().nullable(),
  price: z.number().finite().min(0, { message: 'validation.number_min' }),
  discount_percent: z.number().finite().min(0).max(100).nullable(),
  purchase_price: z.number().finite().min(0).nullable(),
  stock: z.number().int().min(0, { message: 'validation.number_min' }),
  weight_kg: z.number().finite().min(0).nullable(),
  is_double_clutch: z.boolean(),
  has_card: z.boolean(),
  security_level: z.union([securityLevels, z.null()]),
  competitor_url: z.union([z.null(), z.string().url({ message: 'validation.url' }).max(2048)]),
  is_extra_keys_available: z.boolean(),
  extra_key_unit_price: z.number().finite().min(0).nullable(),
  is_featured: z.boolean(),
  is_active: z.boolean(),
  feature_ids: z.array(z.number().int()),
});

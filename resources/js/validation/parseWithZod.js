import { issueToMessage } from './issueToMessage';

/**
 * @param {import('zod').ZodType} schema
 * @param {unknown} data
 * @param {import('i18next').TFunction} t
 * @returns {{ ok: true, data: unknown } | { ok: false, fieldErrors: Record<string, string>, firstError: string }}
 */
export function parseWithZod(schema, data, t) {
  const result = schema.safeParse(data);
  if (result.success) {
    return { ok: true, data: result.data };
  }
  const fieldErrors = {};
  for (const issue of result.error.issues) {
    const key = issue.path.length ? String(issue.path[0]) : '_form';
    if (fieldErrors[key] == null) {
      fieldErrors[key] = issueToMessage(issue, t);
    }
  }
  const first = result.error.issues[0];
  return {
    ok: false,
    fieldErrors,
    firstError: first ? issueToMessage(first, t) : t('validation.invalid'),
  };
}

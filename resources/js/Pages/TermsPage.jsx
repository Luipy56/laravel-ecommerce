import React, { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';

function Section({ title, children }) {
  return (
    <section className="card bg-base-100 border border-base-300 p-4 sm:p-6 flex flex-col gap-3">
      <h2 className="text-lg font-semibold text-base-content">{title}</h2>
      <div className="text-sm text-base-content/80 flex flex-col gap-2">{children}</div>
    </section>
  );
}

function parseTerms(raw) {
  if (!raw || !raw.trim()) return { heading: null, sections: [] };

  const blocks = raw.split(/\n{2,}/).map((b) => b.trim()).filter(Boolean);
  if (blocks.length === 0) return { heading: null, sections: [] };

  const sectionPattern = /^\d+\.\s+/;

  let heading = null;
  const sections = [];
  let current = null;

  for (const block of blocks) {
    if (!heading && !sectionPattern.test(block)) {
      heading = block;
      continue;
    }

    if (sectionPattern.test(block)) {
      if (current) sections.push(current);
      const lines = block.split('\n');
      const title = lines[0].trim();
      const body = lines.slice(1).join('\n').trim();
      current = { title, body: body ? [body] : [] };
    } else if (current) {
      current.body.push(block);
    } else {
      sections.push({ title: null, body: [block] });
    }
  }
  if (current) sections.push(current);

  return { heading, sections };
}

export default function TermsPage() {
  const { t, i18n } = useTranslation();
  const [content, setContent] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get('shop/public-settings')
      .then(({ data }) => {
        if (data.success && data.data) {
          const lang = i18n.language?.slice(0, 2) ?? 'ca';
          const key = `terms_${lang}`;
          const text = data.data[key] || data.data.terms_ca || data.data.terms_es || data.data.terms_en || '';
          setContent(text);
        }
      })
      .catch(() => setContent(''))
      .finally(() => setLoading(false));
  }, [i18n.language]);

  const { heading, sections } = useMemo(() => parseTerms(content), [content]);

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl space-y-8 pb-12">
      <PageTitle title={t('terms.title')} />

      {loading ? (
        <div className="flex justify-center py-12">
          <span className="loading loading-spinner loading-lg" aria-hidden="true" />
        </div>
      ) : sections.length === 0 ? (
        <div className="card bg-base-100 border border-base-300 p-4 sm:p-6">
          <p className="text-sm text-base-content/60">{t('terms.empty')}</p>
        </div>
      ) : (
        <>
          {heading && (
            <div className="text-sm text-base-content/70 border-b border-base-300 pb-4">
              {heading}
            </div>
          )}

          {sections.map((section, i) => (
            <Section key={i} title={section.title ?? `${i + 1}.`}>
              {section.body.map((para, j) => (
                <p key={j} className="whitespace-pre-line">{para}</p>
              ))}
            </Section>
          ))}
        </>
      )}
    </div>
  );
}

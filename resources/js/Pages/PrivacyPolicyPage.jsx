import React from 'react';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';

function Section({ title, children }) {
  return (
    <section className="card bg-base-100 border border-base-300 p-4 sm:p-6 flex flex-col gap-3">
      <h2 className="text-lg font-semibold text-base-content">{title}</h2>
      <div className="text-sm text-base-content/80 flex flex-col gap-2">{children}</div>
    </section>
  );
}

function SubSection({ title, children }) {
  return (
    <div className="flex flex-col gap-1">
      <h3 className="text-sm font-semibold text-base-content">{title}</h3>
      <div className="text-sm text-base-content/80">{children}</div>
    </div>
  );
}

function InfoRow({ label, value }) {
  return (
    <div className="flex flex-wrap gap-x-3 gap-y-0.5">
      <span className="font-medium text-base-content/70 min-w-32">{label}:</span>
      <span className="text-base-content/90">{value}</span>
    </div>
  );
}

export default function PrivacyPolicyPage() {
  const { t } = useTranslation();
  const contactEmail = 'empresa@serralleriasolidaria.cat';

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl space-y-8 pb-12">
      <PageTitle title={t('privacy.title')} />

      <div className="text-sm text-base-content/70 border-b border-base-300 pb-4 flex flex-col gap-1">
        <span>{t('privacy.version_date', { date: '2026-05-05' })}</span>
        <span className="text-xs">{t('privacy.lssi_note')}</span>
      </div>

      {/* 1. Data Controller */}
      <Section title={t('privacy.s1_title')}>
        <p>{t('privacy.s1_body')}</p>
        <div className="flex flex-col gap-1 mt-1">
          <InfoRow label={t('privacy.s1_nif')} value={t('privacy.s1_nif_value')} />
          <InfoRow label={t('privacy.s1_registry')} value={t('privacy.s1_registry_value')} />
          <InfoRow label={t('privacy.s1_rep')} value={t('privacy.s1_rep_value')} />
          <InfoRow label={t('privacy.s1_address')} value={t('privacy.s1_address_value')} />
          <InfoRow label={t('privacy.s1_phone')} value={t('privacy.s1_phone_value')} />
        </div>
        <p>
          {t('privacy.s1_contact')}{' '}
          <a href={`mailto:${contactEmail}`} className="link link-primary">
            {contactEmail}
          </a>
        </p>
      </Section>

      {/* 2. Data We Collect */}
      <Section title={t('privacy.s2_title')}>
        <p>{t('privacy.s2_intro')}</p>
        <SubSection title={t('privacy.s2_1_title')}>
          {t('privacy.s2_1_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_2_title')}>
          {t('privacy.s2_2_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_3_title')}>
          {t('privacy.s2_3_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_4_title')}>
          {t('privacy.s2_4_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_5_title')}>
          {t('privacy.s2_5_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_6_title')}>
          {t('privacy.s2_6_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_7_title')}>
          {t('privacy.s2_7_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_8_title')}>
          {t('privacy.s2_8_body')}
        </SubSection>
        <SubSection title={t('privacy.s2_9_title')}>
          {t('privacy.s2_9_body')}
        </SubSection>
      </Section>

      {/* 3. Purposes and Legal Basis */}
      <Section title={t('privacy.s3_title')}>
        <p>{t('privacy.s3_intro')}</p>
        <div className="overflow-x-auto">
          <table className="table table-sm w-full">
            <thead>
              <tr>
                <th>{t('privacy.s3_col_purpose')}</th>
                <th>{t('privacy.s3_col_data')}</th>
                <th>{t('privacy.s3_col_basis')}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{t('privacy.s3_r1_purpose')}</td>
                <td>{t('privacy.s3_r1_data')}</td>
                <td>{t('privacy.s3_r1_basis')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s3_r2_purpose')}</td>
                <td>{t('privacy.s3_r2_data')}</td>
                <td>{t('privacy.s3_r2_basis')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s3_r3_purpose')}</td>
                <td>{t('privacy.s3_r3_data')}</td>
                <td>{t('privacy.s3_r3_basis')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s3_r4_purpose')}</td>
                <td>{t('privacy.s3_r4_data')}</td>
                <td>{t('privacy.s3_r4_basis')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s3_r5_purpose')}</td>
                <td>{t('privacy.s3_r5_data')}</td>
                <td>{t('privacy.s3_r5_basis')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s3_r6_purpose')}</td>
                <td>{t('privacy.s3_r6_data')}</td>
                <td>{t('privacy.s3_r6_basis')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s3_r7_purpose')}</td>
                <td>{t('privacy.s3_r7_data')}</td>
                <td>{t('privacy.s3_r7_basis')}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </Section>

      {/* 4. Retention Periods */}
      <Section title={t('privacy.s4_title')}>
        <p>{t('privacy.s4_intro')}</p>
        <div className="overflow-x-auto">
          <table className="table table-sm w-full">
            <thead>
              <tr>
                <th>{t('privacy.s4_col_category')}</th>
                <th>{t('privacy.s4_col_period')}</th>
                <th>{t('privacy.s4_col_rule')}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{t('privacy.s4_r1_cat')}</td>
                <td>{t('privacy.s4_r1_period')}</td>
                <td>{t('privacy.s4_r1_rule')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s4_r2_cat')}</td>
                <td>{t('privacy.s4_r2_period')}</td>
                <td>{t('privacy.s4_r2_rule')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s4_r3_cat')}</td>
                <td>{t('privacy.s4_r3_period')}</td>
                <td>{t('privacy.s4_r3_rule')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s4_r4_cat')}</td>
                <td>{t('privacy.s4_r4_period')}</td>
                <td>{t('privacy.s4_r4_rule')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s4_r5_cat')}</td>
                <td>{t('privacy.s4_r5_period')}</td>
                <td>{t('privacy.s4_r5_rule')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s4_r6_cat')}</td>
                <td>{t('privacy.s4_r6_period')}</td>
                <td>{t('privacy.s4_r6_rule')}</td>
              </tr>
              <tr>
                <td>{t('privacy.s4_r7_cat')}</td>
                <td>{t('privacy.s4_r7_period')}</td>
                <td>{t('privacy.s4_r7_rule')}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </Section>

      {/* 5. Recipients */}
      <Section title={t('privacy.s5_title')}>
        <p>{t('privacy.s5_intro')}</p>
        <ul className="list-disc list-inside space-y-1">
          <li>{t('privacy.s5_hosting')}</li>
          <li>
            {t('privacy.s5_stripe')}{' '}
            <a
              href="https://stripe.com/privacy"
              target="_blank"
              rel="noopener noreferrer"
              className="link link-primary"
            >
              stripe.com/privacy
            </a>
          </li>
          <li>
            {t('privacy.s5_paypal')}{' '}
            <a
              href="https://www.paypal.com/privacy"
              target="_blank"
              rel="noopener noreferrer"
              className="link link-primary"
            >
              paypal.com/privacy
            </a>
          </li>
          <li>{t('privacy.s5_email_provider')}</li>
          <li>{t('privacy.s5_backup')}</li>
        </ul>
        <p className="text-xs text-base-content/60">{t('privacy.s5_note')}</p>
      </Section>

      {/* 6. Security */}
      <Section title={t('privacy.s6_title')}>
        <p>{t('privacy.s6_intro')}</p>
        <ul className="list-disc list-inside space-y-1">
          <li>{t('privacy.s6_tls')}</li>
          <li>{t('privacy.s6_encryption')}</li>
          <li>{t('privacy.s6_passwords')}</li>
          <li>{t('privacy.s6_access')}</li>
          <li>{t('privacy.s6_webhooks')}</li>
          <li>{t('privacy.s6_sessions')}</li>
        </ul>
      </Section>

      {/* 7. Your Rights */}
      <Section title={t('privacy.s7_title')}>
        <p>{t('privacy.s7_intro')}</p>
        <ul className="list-disc list-inside space-y-1">
          <li>{t('privacy.s7_access')}</li>
          <li>{t('privacy.s7_rectification')}</li>
          <li>{t('privacy.s7_erasure')}</li>
          <li>{t('privacy.s7_restriction')}</li>
          <li>{t('privacy.s7_portability')}</li>
          <li>{t('privacy.s7_object')}</li>
          <li>{t('privacy.s7_withdraw_consent')}</li>
        </ul>
        <p>
          {t('privacy.s7_contact_text')}{' '}
          <a href={`mailto:${contactEmail}`} className="link link-primary">
            {contactEmail}
          </a>
        </p>
        <p>
          {t('privacy.s7_aepd')}{' '}
          <a
            href="https://www.aepd.es"
            target="_blank"
            rel="noopener noreferrer"
            className="link link-primary"
          >
            aepd.es
          </a>
        </p>
      </Section>

      {/* 8. Automated Decision-Making */}
      <Section title={t('privacy.s8_title')}>
        <p>{t('privacy.s8_body')}</p>
      </Section>

      {/* 9. International Transfers */}
      <Section title={t('privacy.s9_title')}>
        <p>{t('privacy.s9_body')}</p>
      </Section>

      {/* 10. Policy Changes */}
      <Section title={t('privacy.s10_title')}>
        <p>{t('privacy.s10_body')}</p>
      </Section>

      <div className="divider">{t('common.legal_notice', 'Avís Legal · LSSI-CE')}</div>

      {/* 11. Terms of Use */}
      <Section title={t('privacy.s11_title')}>
        <p>{t('privacy.s11_body')}</p>
        <SubSection title={t('privacy.s11_disclaimer_title')}>
          {t('privacy.s11_disclaimer')}
        </SubSection>
      </Section>

      {/* 12. Links Policy */}
      <Section title={t('privacy.s12_title')}>
        <p>{t('privacy.s12_body')}</p>
      </Section>

      {/* 13. Intellectual Property */}
      <Section title={t('privacy.s13_title')}>
        <p>{t('privacy.s13_body')}</p>
      </Section>

      {/* 14. Applicable Law */}
      <Section title={t('privacy.s14_title')}>
        <p>{t('privacy.s14_body')}</p>
      </Section>
    </div>
  );
}

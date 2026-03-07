import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { IconChevronDown, IconChevronRight } from '../../components/icons';

function clientTypeLabel(type, t) {
  if (type === 'person') return t('admin.clients.type_person');
  if (type === 'company') return t('admin.clients.type_company');
  return type || '';
}

function addressTypeLabel(type, t) {
  if (type === 'shipping') return t('profile.address_type_shipping');
  if (type === 'installation') return t('profile.address_type_installation');
  return t('profile.address_type_other');
}

export default function AdminClientShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [client, setClient] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [contactsOpen, setContactsOpen] = useState(false);
  const [addressesOpen, setAddressesOpen] = useState(false);

  const fetchClient = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/clients/${id}`);
      if (data.success) setClient(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchClient();
  }, [fetchClient]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/clients" className="btn btn-ghost btn-sm">
            {t('common.back')}
          </Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !client) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.clients.ficha')}</PageTitle>
        <Link to="/admin/clients" className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="font-semibold text-lg border-b border-base-300 pb-2 mb-4">{t('admin.clients.title')}</h2>
          <dl className="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <div>
              <dt className="text-sm text-base-content/70">{t('admin.clients.email')}</dt>
              <dd>{client.login_email}</dd>
            </div>
            <div>
              <dt className="text-sm text-base-content/70">{t('admin.clients.filter_type')}</dt>
              <dd>{clientTypeLabel(client.type, t)}</dd>
            </div>
            <div>
              <dt className="text-sm text-base-content/70">{t('admin.clients.identification')}</dt>
              <dd>{client.identification}</dd>
            </div>
            <div>
              <dt className="text-sm text-base-content/70">{t('admin.products.is_active')}</dt>
              <dd>{client.is_active ? t('common.yes') : t('common.no')}</dd>
            </div>
          </dl>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body p-0">
          <button
            type="button"
            className="w-full px-6 py-4 text-left font-semibold text-lg border-b border-base-300 flex items-center justify-between gap-2 cursor-pointer hover:bg-base-200/50 transition-colors rounded-t-2xl"
            onClick={() => setContactsOpen((v) => !v)}
            aria-expanded={contactsOpen}
          >
            <span>{t('admin.clients.contacts')} ({client.contacts?.length ?? 0})</span>
            <span className="shrink-0" aria-hidden="true">
              {contactsOpen ? <IconChevronDown className="h-5 w-5" /> : <IconChevronRight className="h-5 w-5" />}
            </span>
          </button>
          <div
            className="grid transition-[grid-template-rows] duration-300 ease-out"
            style={{ gridTemplateRows: contactsOpen ? '1fr' : '0fr' }}
          >
            <div className="min-h-0 overflow-hidden">
              <div className="px-6 pb-4 pt-2">
                {!client.contacts?.length ? (
                  <p className="text-base-content/70">{t('admin.clients.no_contacts')}</p>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="table table-zebra table-sm [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap">
                      <thead>
                        <tr>
                          <th>{t('profile.name')}</th>
                          <th>{t('profile.surname')}</th>
                          <th>{t('profile.phone')}</th>
                          <th>{t('admin.clients.phone2')}</th>
                          <th>{t('admin.clients.email')}</th>
                          <th className="text-center">{t('admin.clients.primary')}</th>
                        </tr>
                      </thead>
                      <tbody>
                        {client.contacts.map((c) => (
                          <tr key={c.id}>
                            <td>{c.name}</td>
                            <td>{c.surname}</td>
                            <td>{c.phone}</td>
                            <td>{c.phone2}</td>
                            <td>{c.email}</td>
                            <td className="text-center">{c.is_primary ? t('common.yes') : ''}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body p-0">
          <button
            type="button"
            className="w-full px-6 py-4 text-left font-semibold text-lg border-b border-base-300 flex items-center justify-between gap-2 cursor-pointer hover:bg-base-200/50 transition-colors rounded-t-2xl"
            onClick={() => setAddressesOpen((v) => !v)}
            aria-expanded={addressesOpen}
          >
            <span>{t('admin.clients.addresses')} ({client.addresses?.length ?? 0})</span>
            <span className="shrink-0" aria-hidden="true">
              {addressesOpen ? <IconChevronDown className="h-5 w-5" /> : <IconChevronRight className="h-5 w-5" />}
            </span>
          </button>
          <div
            className="grid transition-[grid-template-rows] duration-300 ease-out"
            style={{ gridTemplateRows: addressesOpen ? '1fr' : '0fr' }}
          >
            <div className="min-h-0 overflow-hidden">
              <div className="px-6 pb-4 pt-2">
                {!client.addresses?.length ? (
                  <p className="text-base-content/70">{t('admin.clients.no_addresses')}</p>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="table table-zebra table-sm [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap">
                      <thead>
                        <tr>
                          <th>{t('profile.address_type')}</th>
                          <th>{t('admin.clients.address_label')}</th>
                          <th>{t('profile.street')}</th>
                          <th>{t('profile.city')}</th>
                          <th>{t('profile.postal_code')}</th>
                          <th>{t('profile.province')}</th>
                          <th className="text-center">{t('admin.clients.primary')}</th>
                        </tr>
                      </thead>
                      <tbody>
                        {client.addresses.map((a) => (
                          <tr key={a.id}>
                            <td>{addressTypeLabel(a.type, t)}</td>
                            <td>{a.label}</td>
                            <td>{a.street}</td>
                            <td>{a.city}</td>
                            <td>{a.postal_code}</td>
                            <td>{a.province}</td>
                            <td className="text-center">{a.is_primary ? t('common.yes') : ''}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

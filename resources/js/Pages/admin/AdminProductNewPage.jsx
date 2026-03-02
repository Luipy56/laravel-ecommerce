import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminProductForm from '../../components/admin/AdminProductForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminProductNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [categories, setCategories] = useState([]);
  const [variantGroups, setVariantGroups] = useState([]);
  const [features, setFeatures] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const fetchCategories = useCallback(async () => {
    try {
      const { data } = await api.get('admin/categories');
      if (data.success) setCategories(data.data || []);
    } catch {
      setCategories([]);
    }
  }, []);

  const fetchVariantGroups = useCallback(async () => {
    try {
      const { data } = await api.get('admin/variant-groups');
      if (data.success) setVariantGroups(data.data || []);
    } catch {
      setVariantGroups([]);
    }
  }, []);

  const fetchFeatures = useCallback(async () => {
    try {
      const { data } = await api.get('admin/features', { params: { active_only: 1 } });
      if (data.success) setFeatures(data.data || []);
    } catch {
      setFeatures([]);
    }
  }, []);

  useEffect(() => {
    fetchCategories();
    fetchVariantGroups();
    fetchFeatures();
  }, [fetchCategories, fetchVariantGroups, fetchFeatures]);

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/products', payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/products');
      }
      else setError(data.message || t('common.error'));
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.errors?.name?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.products.new')}</PageTitle>
        <Link to="/admin/products" className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminProductForm
            categories={categories}
            variantGroups={variantGroups}
            features={features}
            onSubmit={handleSubmit}
            loading={loading}
            error={error}
          />
        </div>
      </div>
    </div>
  );
}

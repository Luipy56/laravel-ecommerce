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
      const { data } = await api.get('admin/categories', { params: { per_page: 500 } });
      if (data.success) setCategories(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setCategories([]);
    }
  }, [navigate]);

  const fetchVariantGroups = useCallback(async () => {
    try {
      const { data } = await api.get('admin/variant-groups');
      if (data.success) setVariantGroups(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setVariantGroups([]);
    }
  }, [navigate]);

  const fetchFeatures = useCallback(async () => {
    try {
      const { data } = await api.get('admin/features', { params: { active_only: 1 } });
      if (data.success) setFeatures(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setFeatures([]);
    }
  }, [navigate]);

  useEffect(() => {
    fetchCategories();
    fetchVariantGroups();
    fetchFeatures();
  }, [fetchCategories, fetchVariantGroups, fetchFeatures]);

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      // JSON create first — nested `translations` break when coerced through FormData
      // (`"[object Object]"` → Laravel "translations must be an array").
      const { files, ...rest } = payload;
      const { data } = await api.post('admin/products', rest);
      if (!data.success || !data.data?.id) {
        setError(data.message || t('common.error'));
        return;
      }
      if (files?.length) {
        const formData = new FormData();
        files.forEach((f) => formData.append('images[]', f));
        await api.post(`admin/products/${data.data.id}/images`, formData);
      }
      showSuccess(t('common.saved'));
      navigate('/admin/products');
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else {
        const errs = err.response?.data?.errors;
        const first =
          err.response?.data?.message ||
          (errs && typeof errs === 'object' ? Object.values(errs).flat().find(Boolean) : null);
        setError(first || t('common.error'));
      }
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

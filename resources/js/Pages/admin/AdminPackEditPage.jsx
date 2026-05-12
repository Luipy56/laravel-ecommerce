import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminPackForm from '../../components/admin/AdminPackForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminPackEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [pack, setPack] = useState(null);
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [imagesLoading, setImagesLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchPack = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/packs/${id}`);
      if (data.success) setPack(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  const fetchProducts = useCallback(async () => {
    try {
      const { data } = await api.get('admin/products', { params: { per_page: 500 } });
      if (data.success) setProducts(data.data || []);
    } catch {
      setProducts([]);
    }
  }, []);

  useEffect(() => {
    fetchPack();
  }, [fetchPack]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const handleSubmit = async (payload) => {
    setSubmitError('');
    setLoading(true);
    try {
      const { data } = await api.put(`admin/packs/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        setPack(data.data);
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      setSubmitError(err.response?.data?.message || err.response?.data?.errors?.name?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const handleAddImages = async (files) => {
    if (!files?.length) return;
    setSubmitError('');
    setImagesLoading(true);
    try {
      const formData = new FormData();
      files.forEach((f) => formData.append('images[]', f));
      const { data } = await api.post(`admin/packs/${id}/images`, formData);
      if (data.success) {
        showSuccess(t('common.saved'));
        setPack(data.data);
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setSubmitError(err.response?.data?.message || t('common.error'));
    } finally {
      setImagesLoading(false);
    }
  };

  const handleRemoveImage = async (imageId) => {
    setSubmitError('');
    setImagesLoading(true);
    try {
      const { data } = await api.delete(`admin/packs/${id}/images/${imageId}`);
      if (data.success) {
        setPack((p) => ({
          ...p,
          images: (p.images || []).filter((img) => img.id !== imageId),
        }));
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setSubmitError(err.response?.data?.message || t('common.error'));
    } finally {
      setImagesLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to={`/admin/packs/${id}`} className="btn btn-ghost btn-sm">
            {t('common.back')}
          </Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !pack) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.packs.edit')} · {pack.name}</PageTitle>
        <Link to={`/admin/packs/${id}`} className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminPackForm
            pack={pack}
            products={products}
            onSubmit={handleSubmit}
            onAddImages={handleAddImages}
            onRemoveImage={handleRemoveImage}
            loading={loading}
            imagesLoading={imagesLoading}
            error={submitError}
          />
        </div>
      </div>
    </div>
  );
}

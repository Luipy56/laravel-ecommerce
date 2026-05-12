import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminProductForm from '../../components/admin/AdminProductForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminProductEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [categories, setCategories] = useState([]);
  const [variantGroups, setVariantGroups] = useState([]);
  const [features, setFeatures] = useState([]);
  const [loading, setLoading] = useState(false);
  const [imagesLoading, setImagesLoading] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchProduct = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/products/${id}`);
      if (data.success) setProduct(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    }
  }, [id, navigate, t]);

  const fetchCategories = useCallback(async () => {
    try {
      const { data } = await api.get('admin/categories', { params: { per_page: 500 } });
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
    fetchProduct();
  }, [fetchProduct]);

  useEffect(() => {
    fetchCategories();
    fetchVariantGroups();
    fetchFeatures();
  }, [fetchCategories, fetchVariantGroups, fetchFeatures]);

  const handleSubmit = async (payload) => {
    setSubmitError('');
    setLoading(true);
    try {
      const { files: _, ...rest } = payload;
      const { data } = await api.put(`admin/products/${id}`, rest);
      if (data.success) {
        showSuccess(t('common.saved'));
        setProduct(data.data);
      } else {
        setSubmitError(data.message || t('common.error'));
      }
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
      const { data } = await api.post(`admin/products/${id}/images`, formData);
      if (data.success) {
        showSuccess(t('common.saved'));
        setProduct(data.data);
      } else {
        setSubmitError(data.message || t('common.error'));
      }
    } catch (err) {
      setSubmitError(err.response?.data?.message || t('common.error'));
    } finally {
      setImagesLoading(false);
    }
  };

  const handleRemoveImage = async (imageId) => {
    setSubmitError('');
    setImagesLoading(true);
    try {
      const { data } = await api.delete(`admin/products/${id}/images/${imageId}`);
      if (data.success) {
        setProduct((p) => ({
          ...p,
          images: (p.images || []).filter((img) => img.id !== imageId),
        }));
      } else {
        setSubmitError(data.message || t('common.error'));
      }
    } catch (err) {
      setSubmitError(err.response?.data?.message || t('common.error'));
    } finally {
      setImagesLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to={`/admin/products/${id}`} className="btn btn-ghost btn-sm">
            {t('common.back')}
          </Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.products.edit')} · {product.name || product.code || id}</PageTitle>
        <Link to={`/admin/products/${id}`} className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminProductForm
            product={product}
            categories={categories}
            variantGroups={variantGroups}
            features={features}
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

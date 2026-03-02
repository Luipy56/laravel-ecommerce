import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { CartProvider } from './contexts/CartContext';
import Layout from './components/Layout';
import AdminLayout from './components/admin/AdminLayout';
import HomePage from './Pages/HomePage';
import ProductListPage from './Pages/ProductListPage';
import ProductDetailPage from './Pages/ProductDetailPage';
import PackDetailPage from './Pages/PackDetailPage';
import CartPage from './Pages/CartPage';
import LoginPage from './Pages/LoginPage';
import RegisterPage from './Pages/RegisterPage';
import CheckoutPage from './Pages/CheckoutPage';
import OrdersPage from './Pages/OrdersPage';
import OrderDetailPage from './Pages/OrderDetailPage';
import ProfilePage from './Pages/ProfilePage';
import CustomSolutionPage from './Pages/CustomSolutionPage';
import AdminLoginPage from './Pages/admin/AdminLoginPage';
import AdminDashboardPage from './Pages/admin/AdminDashboardPage';
import AdminProductsPage from './Pages/admin/AdminProductsPage';
import AdminProductNewPage from './Pages/admin/AdminProductNewPage';
import AdminProductShowPage from './Pages/admin/AdminProductShowPage';
import AdminProductEditPage from './Pages/admin/AdminProductEditPage';
import AdminFeaturesPage from './Pages/admin/AdminFeaturesPage';
import AdminFeatureNewPage from './Pages/admin/AdminFeatureNewPage';
import AdminFeatureShowPage from './Pages/admin/AdminFeatureShowPage';
import AdminFeatureEditPage from './Pages/admin/AdminFeatureEditPage';
import AdminFeatureNamesPage from './Pages/admin/AdminFeatureNamesPage';
import AdminFeatureNameNewPage from './Pages/admin/AdminFeatureNameNewPage';
import AdminFeatureNameShowPage from './Pages/admin/AdminFeatureNameShowPage';
import AdminFeatureNameEditPage from './Pages/admin/AdminFeatureNameEditPage';
import AdminPacksPage from './Pages/admin/AdminPacksPage';
import AdminPackNewPage from './Pages/admin/AdminPackNewPage';
import AdminPackShowPage from './Pages/admin/AdminPackShowPage';
import AdminPackEditPage from './Pages/admin/AdminPackEditPage';
import AdminVariantGroupsPage from './Pages/admin/AdminVariantGroupsPage';
import AdminVariantGroupNewPage from './Pages/admin/AdminVariantGroupNewPage';
import AdminVariantGroupShowPage from './Pages/admin/AdminVariantGroupShowPage';
import AdminVariantGroupEditPage from './Pages/admin/AdminVariantGroupEditPage';
import AdminClientsPage from './Pages/admin/AdminClientsPage';
import AdminClientShowPage from './Pages/admin/AdminClientShowPage';
import AdminAdminsPage from './Pages/admin/AdminAdminsPage';
import AdminAdminShowPage from './Pages/admin/AdminAdminShowPage';
import AdminAdminNewPage from './Pages/admin/AdminAdminNewPage';
import AdminAdminEditPage from './Pages/admin/AdminAdminEditPage';
import AdminOrdersPage from './Pages/admin/AdminOrdersPage';
import AdminOrderShowPage from './Pages/admin/AdminOrderShowPage';
import AdminOrderEditPage from './Pages/admin/AdminOrderEditPage';
import AdminPersonalizedSolutionsPage from './Pages/admin/AdminPersonalizedSolutionsPage';
import AdminPersonalizedSolutionShowPage from './Pages/admin/AdminPersonalizedSolutionShowPage';
import AdminPersonalizedSolutionEditPage from './Pages/admin/AdminPersonalizedSolutionEditPage';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <CartProvider>
          <Routes>
            <Route path="/" element={<Layout />}>
              <Route index element={<HomePage />} />
              <Route path="products" element={<ProductListPage />} />
              <Route path="products/:id" element={<ProductDetailPage />} />
              <Route path="packs/:id" element={<PackDetailPage />} />
              <Route path="categories/:id/products" element={<ProductListPage />} />
              <Route path="cart" element={<CartPage />} />
              <Route path="login" element={<LoginPage />} />
              <Route path="register" element={<RegisterPage />} />
              <Route path="checkout" element={<CheckoutPage />} />
              <Route path="orders" element={<OrdersPage />} />
              <Route path="orders/:id" element={<OrderDetailPage />} />
              <Route path="profile" element={<ProfilePage />} />
              <Route path="custom-solution" element={<CustomSolutionPage />} />
            </Route>
            <Route path="admin/login" element={<AdminLoginPage />} />
            <Route path="admin" element={<AdminLayout />}>
              <Route index element={<AdminDashboardPage />} />
              <Route path="products" element={<AdminProductsPage />} />
              <Route path="products/new" element={<AdminProductNewPage />} />
              <Route path="products/:id/edit" element={<AdminProductEditPage />} />
              <Route path="products/:id" element={<AdminProductShowPage />} />
              <Route path="features" element={<AdminFeaturesPage />} />
              <Route path="features/new" element={<AdminFeatureNewPage />} />
              <Route path="features/:id/edit" element={<AdminFeatureEditPage />} />
              <Route path="features/:id" element={<AdminFeatureShowPage />} />
              <Route path="feature-names" element={<AdminFeatureNamesPage />} />
              <Route path="feature-names/new" element={<AdminFeatureNameNewPage />} />
              <Route path="feature-names/:id/edit" element={<AdminFeatureNameEditPage />} />
              <Route path="feature-names/:id" element={<AdminFeatureNameShowPage />} />
              <Route path="packs" element={<AdminPacksPage />} />
              <Route path="packs/new" element={<AdminPackNewPage />} />
              <Route path="packs/:id/edit" element={<AdminPackEditPage />} />
              <Route path="packs/:id" element={<AdminPackShowPage />} />
              <Route path="variant-groups" element={<AdminVariantGroupsPage />} />
              <Route path="variant-groups/new" element={<AdminVariantGroupNewPage />} />
              <Route path="variant-groups/:id/edit" element={<AdminVariantGroupEditPage />} />
              <Route path="variant-groups/:id" element={<AdminVariantGroupShowPage />} />
              <Route path="clients" element={<AdminClientsPage />} />
              <Route path="clients/:id" element={<AdminClientShowPage />} />
              <Route path="orders" element={<AdminOrdersPage />} />
              <Route path="orders/:id/edit" element={<AdminOrderEditPage />} />
              <Route path="orders/:id" element={<AdminOrderShowPage />} />
              <Route path="admins" element={<AdminAdminsPage />} />
              <Route path="admins/new" element={<AdminAdminNewPage />} />
              <Route path="admins/:id/edit" element={<AdminAdminEditPage />} />
              <Route path="admins/:id" element={<AdminAdminShowPage />} />
              <Route path="personalized-solutions" element={<AdminPersonalizedSolutionsPage />} />
              <Route path="personalized-solutions/:id/edit" element={<AdminPersonalizedSolutionEditPage />} />
              <Route path="personalized-solutions/:id" element={<AdminPersonalizedSolutionShowPage />} />
            </Route>
          </Routes>
        </CartProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}

import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { CartProvider } from './contexts/CartContext';
import Layout from './components/Layout';
import HomePage from './Pages/HomePage';
import ProductListPage from './Pages/ProductListPage';
import ProductDetailPage from './Pages/ProductDetailPage';
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

function AdminLayout() {
  return (
    <div className="min-h-screen bg-base-200 p-4">
      <div className="navbar bg-base-100 rounded-lg shadow mb-4">
        <span className="text-xl font-bold">Admin</span>
        <a href="/" className="btn btn-ghost btn-sm">Tornar a la botiga</a>
      </div>
      <AdminDashboardPage />
    </div>
  );
}

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <CartProvider>
          <Routes>
            <Route path="/" element={<Layout />}>
              <Route index element={<HomePage />} />
              <Route path="products" element={<ProductListPage />} />
              <Route path="products/:id" element={<ProductDetailPage />} />
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
            <Route path="admin" element={<AdminLayout />} />
          </Routes>
        </CartProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}

export default App;

import React from 'react';
import { Outlet } from 'react-router-dom';
import Navbar from './Navbar';
import CartWidget from './CartWidget';
import CartAddedToast from './CartAddedToast';
import Footer from './Footer';
import ScrollToTop from './ScrollToTop';

export default function Layout() {
  return (
    <div className="min-h-screen flex flex-col bg-base-200">
      <Navbar />
      <main className="flex-1 container mx-auto px-4 py-6">
        <Outlet />
      </main>
      <Footer />
      <CartWidget />
      <CartAddedToast />
      <ScrollToTop />
    </div>
  );
}

import { Outlet } from 'react-router-dom'
import Navbar from './Navbar'
import Footer from './Footer'
import CartWidget from './CartWidget'
import ScrollToTop from './ScrollToTop'
import CookieConsentBanner from './CookieConsentBanner'

export default function Layout() {
  return (
    <div className="shop-layout bg-base-200">
      <div className="shop-layout__top">
        <Navbar />
      </div>
      <main id="shop-main-content" className="shop-layout__main">
        <Outlet />
      </main>
      <div className="shop-layout__footer">
        <Footer />
      </div>
      <CartWidget />
      <ScrollToTop />
      <CookieConsentBanner />
    </div>
  )
}

import { Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { FaFacebook, FaInstagram, FaTwitter } from 'react-icons/fa'

export default function Footer() {
  const { t } = useTranslation()
  const currentYear = new Date().getFullYear()

  return (
    <footer className="footer-shop" role="contentinfo">
      <div className="footer-shop__container">
        <div className="footer-shop__grid">
          {/* Col 1: Brand */}
          <div className="footer-shop__brand">
            <p className="footer-shop__brand-logo text-primary">
              {t('app.name', 'La Botiga')}
            </p>
            <p className="footer-shop__brand-description">
              {t('footer.description', 'La teva botiga de confiança. Els millors productes al teu servei.')}
            </p>
          </div>

          {/* Col 2: Quick links */}
          <nav className="footer-shop__links" aria-label={t('footer.links', 'Enllaços')}>
            <h3>{t('footer.links', 'Enllaços')}</h3>
            <ul>
              <li><Link to="/">{t('nav.home', 'Inici')}</Link></li>
              <li><Link to="/products">{t('nav.products', 'Productes')}</Link></li>
              <li><Link to="/cart">{t('nav.cart', 'Carret')}</Link></li>
              <li><Link to="/faq">{t('nav.faq', 'FAQs')}</Link></li>
              <li><Link to="/custom-solution">{t('nav.custom_solution', 'Solucions Personalitzades')}</Link></li>
              <li><Link to="/privacy-policy">{t('footer.privacy', 'Política de privacitat')}</Link></li>
              <li><Link to="/terms">{t('footer.terms', 'Termes i condicions')}</Link></li>
            </ul>
          </nav>

          {/* Col 3: Contact */}
          <div className="footer-shop__contact">
            <h3>{t('footer.contact', 'Contacte')}</h3>
            <ul>
              <li>
                <span>{t('footer.phone', 'Telèfon')}:</span>{' '}
                <a href="tel:+34000000000">+34 000 000 000</a>
              </li>
              <li>
                <span>{t('footer.email', 'Email')}:</span>{' '}
                <a href="mailto:info@labotiga.cat">info@labotiga.cat</a>
              </li>
            </ul>
          </div>

          {/* Col 4: Social + payments */}
          <div className="footer-shop__social">
            <h3>{t('footer.follow_us', 'Segueix-nos')}</h3>
            <div className="footer-shop__social-icons" aria-label={t('footer.social', 'Xarxes socials')}>
              <a href="#" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                <FaFacebook aria-hidden="true" />
              </a>
              <a href="#" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                <FaInstagram aria-hidden="true" />
              </a>
              <a href="#" aria-label="Twitter / X" target="_blank" rel="noopener noreferrer">
                <FaTwitter aria-hidden="true" />
              </a>
            </div>

            <div className="footer-shop__payments">
              <h3>{t('footer.secure_payment', 'Pagament segur')}</h3>
              <p>{t('footer.payment_methods', 'Acceptem targeta de crèdit i PayPal.')}</p>
            </div>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="footer-shop__bottom">
          <div className="footer-shop__bottom-links">
            <Link to="/privacy-policy">{t('footer.privacy', 'Política de privacitat')}</Link>
            <span className="footer-shop__bottom-separator">|</span>
            <Link to="/terms">{t('footer.terms', 'Termes')}</Link>
          </div>
          <p>© {currentYear} {t('app.name', 'La Botiga')}. {t('footer.rights', 'Tots els drets reservats.')} </p>
        </div>
      </div>
    </footer>
  )
}

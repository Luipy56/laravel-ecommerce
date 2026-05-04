import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';
import MiniGameEmbed from '../components/MiniGameEmbed';

const FEATURED_GAME = {
  id: 'dino',
  src: '/games/dino/index.html',
  titleKey: 'games.game_dino_title',
};

export default function ErrorPage({ error, resetError }) {
  const { t } = useTranslation();

  return (
    <div className="mx-auto w-full min-w-0 max-w-2xl py-12 text-center">
      <PageTitle>{t('errors.server_error_title')}</PageTitle>
      <p className="mb-6 text-base-content/70">{t('errors.server_error_body')}</p>

      <div className="flex flex-wrap justify-center gap-3 mb-10">
        <Link to="/" className="btn btn-primary">
          {t('errors.back_home')}
        </Link>
        {resetError && (
          <button className="btn btn-outline" onClick={resetError}>
            {t('errors.reload')}
          </button>
        )}
      </div>

      <div className="divider">{t('games.error_page_section')}</div>

      <p className="mb-4 text-sm text-base-content/60">
        {t('games.while_you_wait')}
      </p>

      <MiniGameEmbed
        src={FEATURED_GAME.src}
        title={t(FEATURED_GAME.titleKey)}
      />

      <div className="mt-4 text-center">
        <Link to="/games" className="link link-primary text-sm">
          {t('games.see_all_games')}
        </Link>
      </div>
    </div>
  );
}

import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';
import MiniGameEmbed from '../components/MiniGameEmbed';
import { GAMES } from '../config/games';

export default function ErrorPage({ resetError }) {
  const { t } = useTranslation();
  const [index, setIndex] = useState(0);

  const game = GAMES[index];
  const prev = () => setIndex(i => (i - 1 + GAMES.length) % GAMES.length);
  const next = () => setIndex(i => (i + 1) % GAMES.length);

  return (
    <div className="mx-auto w-full min-w-0 max-w-2xl">
      <div className="py-8 text-center">
        <PageTitle>{t('errors.server_error_title')}</PageTitle>
        <p className="mb-6 text-base-content/70">{t('errors.server_error_body')}</p>
        <div className="flex flex-wrap justify-center gap-3">
          <Link to="/" className="btn btn-primary">
            {t('errors.back_home')}
          </Link>
          {resetError && (
            <button className="btn btn-outline" onClick={resetError}>
              {t('errors.reload')}
            </button>
          )}
        </div>
      </div>

      <div className="divider">{t('games.error_page_section')}</div>

      <div className="mb-3 flex items-center justify-between gap-2 mt-2">
        <button
          onClick={prev}
          aria-label={t('games.prev_game')}
          className="btn btn-ghost btn-square btn-sm sm:btn-md"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <span className="text-base font-semibold text-base-content">
          {t(game.titleKey)}
          <span className="ml-2 text-xs font-normal text-base-content/40">
            {index + 1} / {GAMES.length}
          </span>
        </span>

        <button
          onClick={next}
          aria-label={t('games.next_game')}
          className="btn btn-ghost btn-square btn-sm sm:btn-md"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <MiniGameEmbed
        key={game.id}
        src={game.src}
        title={t(game.titleKey)}
        className="mb-6"
      />
    </div>
  );
}

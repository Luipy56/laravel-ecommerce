import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';
import MiniGameEmbed from '../components/MiniGameEmbed';

const GAMES = [
  { id: '2048',   src: '/games/2048/index.html',   titleKey: 'games.game_2048_title' },
  { id: 'dino',   src: '/games/dino/index.html',   titleKey: 'games.game_dino_title' },
  { id: 'tetris', src: '/games/tetris/index.html', titleKey: 'games.game_tetris_title' },
];

export default function NotFoundPage() {
  const { t } = useTranslation();
  const [index, setIndex] = useState(0);

  const game = GAMES[index];
  const prev = () => setIndex(i => (i - 1 + GAMES.length) % GAMES.length);
  const next = () => setIndex(i => (i + 1) % GAMES.length);

  return (
    <div className="mx-auto w-full min-w-0 max-w-2xl">
      <div className="py-8 text-center">
        <div className="mb-2 text-7xl font-black text-base-content/10 select-none">404</div>
        <PageTitle>{t('errors.not_found_title')}</PageTitle>
        <p className="text-base-content/70 mb-6">{t('errors.not_found_body')}</p>
        <Link to="/" className="btn btn-primary">
          {t('errors.back_home')}
        </Link>
      </div>

      <div className="divider">{t('games.not_found_section')}</div>

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

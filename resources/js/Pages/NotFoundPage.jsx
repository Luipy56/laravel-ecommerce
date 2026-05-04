import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';
import MiniGameEmbed from '../components/MiniGameEmbed';

const QUICK_GAMES = [
  { id: '2048', src: '/games/2048/index.html', emoji: '🔢', titleKey: 'games.game_2048_title' },
  { id: 'dino', src: '/games/dino/index.html', emoji: '🦕', titleKey: 'games.game_dino_title' },
  { id: 'tetris', src: '/games/tetris/index.html', emoji: '🧩', titleKey: 'games.game_tetris_title' },
];

export default function NotFoundPage() {
  const { t } = useTranslation();
  const [activeGame, setActiveGame] = useState(null);

  const currentGame = QUICK_GAMES.find(g => g.id === activeGame);

  return (
    <div className="mx-auto w-full min-w-0 max-w-2xl">
      <div className="py-10 text-center">
        <div className="mb-2 text-7xl font-black text-base-content/10 select-none">404</div>
        <PageTitle>{t('errors.not_found_title')}</PageTitle>
        <p className="text-base-content/70 mb-6">{t('errors.not_found_body')}</p>
        <Link to="/" className="btn btn-primary">
          {t('errors.back_home')}
        </Link>
      </div>

      <div className="divider">{t('games.not_found_section')}</div>

      {currentGame ? (
        <div className="mt-2">
          <div className="mb-3">
            <button
              className="btn btn-sm btn-ghost"
              onClick={() => setActiveGame(null)}
            >
              {t('games.change_game')}
            </button>
          </div>
          <MiniGameEmbed src={currentGame.src} title={t(currentGame.titleKey)} />
        </div>
      ) : (
        <div className="grid grid-cols-3 gap-3 mt-2 mb-6">
          {QUICK_GAMES.map(game => (
            <button
              key={game.id}
              onClick={() => setActiveGame(game.id)}
              className="card card-border border-base-300 bg-base-100 transition-shadow hover:shadow-md active:scale-[0.98]"
            >
              <div className="card-body items-center gap-1 py-5 text-center">
                <span className="text-3xl">{game.emoji}</span>
                <p className="text-sm font-semibold">{t(game.titleKey)}</p>
                <span className="btn btn-primary btn-xs mt-1">{t('games.play')}</span>
              </div>
            </button>
          ))}
        </div>
      )}

      <div className="mt-4 text-center pb-8">
        <Link to="/games" className="link link-primary text-sm">
          {t('games.see_all_games')}
        </Link>
      </div>
    </div>
  );
}

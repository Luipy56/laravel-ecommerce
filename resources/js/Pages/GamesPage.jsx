import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';
import MiniGameEmbed from '../components/MiniGameEmbed';

const GAMES = [
  {
    id: '2048',
    src: '/games/2048/index.html',
    titleKey: 'games.game_2048_title',
    descKey: 'games.game_2048_desc',
  },
  {
    id: 'dino',
    src: '/games/dino/index.html',
    titleKey: 'games.game_dino_title',
    descKey: 'games.game_dino_desc',
  },
  {
    id: 'tetris',
    src: '/games/tetris/index.html',
    titleKey: 'games.game_tetris_title',
    descKey: 'games.game_tetris_desc',
  },
];

export default function GamesPage() {
  const { t } = useTranslation();
  const [activeGame, setActiveGame] = useState(null);

  const currentGame = GAMES.find(g => g.id === activeGame);

  return (
    <div className="mx-auto w-full min-w-0 max-w-4xl">
      <PageTitle>{t('games.title')}</PageTitle>

      {currentGame ? (
        <div>
          <div className="mb-4">
            <button
              className="btn btn-sm btn-ghost"
              onClick={() => setActiveGame(null)}
            >
              {t('games.back_to_list')}
            </button>
          </div>
          <MiniGameEmbed
            src={currentGame.src}
            title={t(currentGame.titleKey)}
          />
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {GAMES.map(game => (
              <button
                key={game.id}
                onClick={() => setActiveGame(game.id)}
                className="card card-border border-base-300 bg-base-100 text-left transition-shadow hover:shadow-md active:scale-[0.98]"
              >
                <div className="card-body items-center gap-2 py-8 text-center">
                  <h2 className="card-title text-base">{t(game.titleKey)}</h2>
                  <div className="card-actions mt-2">
                    <span className="btn btn-primary btn-sm">{t('games.play')}</span>
                  </div>
                </div>
              </button>
            ))}
          </div>
        </>
      )}
    </div>
  );
}

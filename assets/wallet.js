/* ============================================================
 * DKWallet — общий кошелёк для всех игр сайта diskordk.github.io
 * Однократная зарядка: `<script src="/assets/wallet.js"></script>`
 * Данные лежат в localStorage, origin общий у всех страниц сайта.
 *
 * API:
 *   DKWallet.points()            -> текущий баланс очков
 *   DKWallet.add(n)              -> +n очков, возвращает новый баланс
 *   DKWallet.spend(n)            -> потратить n; вернёт новый баланс или null, если не хватает
 *   DKWallet.stars()             -> ранг (звёзды)
 *   DKWallet.addStars(n)         -> +n звёзд
 *   DKWallet.get()               -> весь профиль целиком { points, stars, totalEarned }
 *   DKWallet.onChange(fn)        -> подписка: баланс/звёзды изменились (включая другие вкладки)
 *   DKWallet.reset()             -> обнулить всё
 *
 * Пример будущей игры:
 *   const price = 50;
 *   const left = DKWallet.spend(price);
 *   if (left === null) alert('Не хватает очков! Заработай их в «Сундуках удачи».');
 * ============================================================ */
(function (global) {
  'use strict';

  const KEY = 'diskordk.profile.v1';
  const DEFAULT = { points: 0, stars: 0, totalEarned: 0, updatedAt: 0 };

  const listeners = [];

  function load() {
    try {
      const raw = global.localStorage.getItem(KEY);
      const data = raw ? JSON.parse(raw) : {};
      return Object.assign({}, DEFAULT, data);
    } catch (e) {
      return Object.assign({}, DEFAULT);
    }
  }

  function save(profile) {
    profile.updatedAt = Date.now();
    global.localStorage.setItem(KEY, JSON.stringify(profile));
    listeners.forEach(fn => fn(profile));      // внутри вкладки
    // между вкладками браузер сам бросит событие "storage"
  }

  global.DKWallet = {
    get: load,
    points() { return load().points; },
    stars()  { return load().stars; },
    add(n) {
      const p = load();
      p.points += n;
      p.totalEarned += n;
      save(p);
      return p.points;
    },
    spend(n) {
      const p = load();
      if (p.points < n) return null;
      p.points -= n;
      save(p);
      return p.points;
    },
    addStars(n) {
      const p = load();
      p.stars += n;
      save(p);
      return p.stars;
    },
    onChange(fn) {
      listeners.push(fn);
      global.addEventListener('storage', e => {
        if (e.key === KEY) fn(load());
      });
    },
    reset() { save(Object.assign({}, DEFAULT)); }
  };
})(window);

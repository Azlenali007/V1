/**
 * 3D Interactive Card Slider with GSAP
 * Supports desktop mouse drag, touch swipe, 3D perspective depth, and responsive scaling.
 */

import { gsap } from 'gsap';

export function init3DCardSlider({
  containerSelector = '#card-slider-container',
  cardsSelector = '.slider-card-item',
  onCardActivate = null,
  initialIndex = 1
} = {}) {
  const container = document.querySelector(containerSelector);
  if (!container) return null;

  const cards = Array.from(container.querySelectorAll(cardsSelector));
  if (!cards.length) return null;

  let activeIndex = Math.min(Math.max(0, initialIndex), cards.length - 1);
  let isDragging = false;
  let startX = 0;
  let currentX = 0;
  let dragThreshold = 45;

  function updateCards(animate = true) {
    const isMobile = window.innerWidth < 768;
    const xOffset = isMobile ? 140 : 260;
    const zOffset = isMobile ? -70 : -100;
    const rotateAngle = isMobile ? 18 : 25;

    cards.forEach((card, index) => {
      const diff = index - activeIndex;
      const duration = animate ? 0.55 : 0;
      const ease = 'power3.out';

      if (diff === 0) {
        // Active Center Card
        card.setAttribute('data-active', 'true');
        gsap.to(card, {
          x: 0,
          z: 60,
          scale: 1,
          rotationY: 0,
          opacity: 1,
          zIndex: 40,
          duration,
          ease,
          filter: 'drop-shadow(0 25px 35px rgba(0,0,0,0.18))'
        });
      } else if (diff === -1) {
        // Left Card
        card.removeAttribute('data-active');
        gsap.to(card, {
          x: -xOffset,
          z: zOffset,
          scale: isMobile ? 0.86 : 0.88,
          rotationY: rotateAngle,
          opacity: 0.82,
          zIndex: 20,
          duration,
          ease,
          filter: 'drop-shadow(0 15px 25px rgba(0,0,0,0.12))'
        });
      } else if (diff === 1) {
        // Right Card
        card.removeAttribute('data-active');
        gsap.to(card, {
          x: xOffset,
          z: zOffset,
          scale: isMobile ? 0.86 : 0.88,
          rotationY: -rotateAngle,
          opacity: 0.82,
          zIndex: 20,
          duration,
          ease,
          filter: 'drop-shadow(0 15px 25px rgba(0,0,0,0.12))'
        });
      } else {
        // Distant / Hidden Cards
        card.removeAttribute('data-active');
        const dir = diff < 0 ? -1 : 1;
        gsap.to(card, {
          x: dir * (xOffset * 1.6),
          z: -180,
          scale: 0.72,
          rotationY: dir * (rotateAngle * 1.3),
          opacity: 0,
          zIndex: 5,
          duration,
          ease
        });
      }
    });

    if (typeof onCardActivate === 'function') {
      onCardActivate(activeIndex, cards[activeIndex]);
    }
  }

  function next() {
    if (activeIndex < cards.length - 1) {
      activeIndex++;
      updateCards();
    }
  }

  function prev() {
    if (activeIndex > 0) {
      activeIndex--;
      updateCards();
    }
  }

  function goTo(index) {
    if (index >= 0 && index < cards.length) {
      activeIndex = index;
      updateCards();
    }
  }

  // Card click handler
  cards.forEach((card, index) => {
    card.addEventListener('click', (e) => {
      if (Math.abs(currentX - startX) > 10) return; // ignore if was drag
      if (index !== activeIndex) {
        e.stopPropagation();
        goTo(index);
      }
    });
  });

  // Touch handlers
  container.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    currentX = startX;
    isDragging = true;
  }, { passive: true });

  container.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    currentX = e.touches[0].clientX;
  }, { passive: true });

  container.addEventListener('touchend', () => {
    if (!isDragging) return;
    isDragging = false;
    const diff = currentX - startX;
    if (diff > dragThreshold) {
      prev();
    } else if (diff < -dragThreshold) {
      next();
    }
    startX = 0;
    currentX = 0;
  });

  // Mouse drag handlers
  container.addEventListener('mousedown', (e) => {
    startX = e.clientX;
    currentX = startX;
    isDragging = true;
    container.style.cursor = 'grabbing';
  });

  window.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    currentX = e.clientX;
  });

  window.addEventListener('mouseup', () => {
    if (!isDragging) return;
    isDragging = false;
    container.style.cursor = 'grab';
    const diff = currentX - startX;
    if (diff > dragThreshold) {
      prev();
    } else if (diff < -dragThreshold) {
      next();
    }
    startX = 0;
    currentX = 0;
  });

  // Resize listener for responsive 3D transforms
  window.addEventListener('resize', () => {
    updateCards(false);
  });

  // Initial draw
  updateCards(false);

  return {
    next,
    prev,
    goTo,
    getActiveIndex: () => activeIndex,
    refresh: () => updateCards(true)
  };
}

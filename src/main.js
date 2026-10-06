/**
 * SMM Panel - Main Application Script
 * Powered by Alpine.js, GSAP & Tailwind CSS
 * ZERO TypeScript, ZERO React
 */

import './index.css';
import Alpine from 'alpinejs';
import { init3DCardSlider } from './slider3d.js';

// Global Alpine Store & App Controller
window.smmApp = function () {
  return {
    currentView: 'landing', // 'landing', 'dashboard', 'new-order', 'add-funds', 'my-orders', 'services', 'transactions', 'support', 'profile', 'admin', 'auth'
    authTab: 'login', // 'login' or 'register'
    theme: 'light',
    toast: { show: false, message: '', type: 'success' },
    landingSliderInstance: null,
    dashboardSliderInstance: null,
    activeLandingDot: 1,

    // Active User State
    user: {
      id: 1024,
      name: 'Aaris Ali',
      email: 'aarisali@gmail.com',
      phone: '+91 98765 43210',
      balance: 850.50,
      role: 'user',
      joined: '10 May 2025',
      isLoggedIn: true
    },

    // Categories matching reference design
    categories: [
      { id: 1, name: 'Instagram', slug: 'instagram', icon: 'instagram' },
      { id: 2, name: 'YouTube', slug: 'youtube', icon: 'youtube' },
      { id: 3, name: 'Telegram', slug: 'telegram', icon: 'telegram' },
      { id: 4, name: 'Facebook', slug: 'facebook', icon: 'facebook' },
      { id: 5, name: 'TikTok', slug: 'tiktok', icon: 'tiktok' },
      { id: 6, name: 'Twitter (X)', slug: 'twitter', icon: 'twitter' }
    ],

    // Featured Hero 3D Cards for Landing Slider (Matching image 20CA04EE-420B-4D6B-8751-19D4C9DD3C7D.png)
    landingCards: [
      {
        id: 5,
        serviceId: 5,
        platform: 'YouTube',
        title: 'YouTube Views',
        meta: 'Real Views • High Retention',
        price: '12',
        speedTag: 'Fast Delivery',
        bgGradient: 'from-rose-500 via-red-500 to-rose-600',
        glowClass: 'card-glow-youtube',
        accentColor: 'text-red-500',
        type: 'youtube'
      },
      {
        id: 1,
        serviceId: 1,
        platform: 'Instagram',
        title: 'Instagram Followers',
        meta: 'Real & Active Followers • High Quality • Fast Delivery',
        price: '35',
        speedTag: 'Starts in 1-2 Hours',
        bgGradient: 'from-amber-400 via-rose-500 to-purple-600',
        glowClass: 'card-glow-insta',
        accentColor: 'text-rose-500',
        type: 'instagram'
      },
      {
        id: 7,
        serviceId: 7,
        platform: 'Telegram',
        title: 'Telegram Members',
        meta: 'Real & Active Members • Instant Start',
        price: '45',
        speedTag: 'Fast Delivery',
        bgGradient: 'from-sky-400 via-blue-500 to-indigo-600',
        glowClass: 'card-glow-telegram',
        accentColor: 'text-sky-500',
        type: 'telegram'
      },
      {
        id: 9,
        serviceId: 9,
        platform: 'TikTok',
        title: 'TikTok Followers',
        meta: 'High Retention • Active Accounts',
        price: '38',
        speedTag: 'Instant Start',
        bgGradient: 'from-slate-800 via-neutral-900 to-black',
        glowClass: 'card-glow-blue',
        accentColor: 'text-cyan-400',
        type: 'tiktok'
      },
      {
        id: 10,
        serviceId: 10,
        platform: 'Twitter (X)',
        title: 'Twitter (X) Followers',
        meta: 'Global Profiles • Fast Delivery',
        price: '55',
        speedTag: 'Fast Delivery',
        bgGradient: 'from-slate-700 via-slate-800 to-slate-950',
        glowClass: 'card-glow-blue',
        accentColor: 'text-slate-300',
        type: 'twitter'
      }
    ],

    // Services Catalog
    services: [
      {
        id: 1,
        categoryId: 1,
        categorySlug: 'instagram',
        name: 'Instagram Followers',
        pricePerK: 35,
        minQty: 1000,
        maxQty: 1010000,
        badge: 'High quality followers | Instant Start | No Drop',
        speed: 'Starts in 1-2 Hours',
        description: 'Real looking high-retention Instagram followers with 30-day refill guarantee.'
      },
      {
        id: 2,
        categoryId: 1,
        categorySlug: 'instagram',
        name: 'Instagram Likes',
        pricePerK: 20,
        minQty: 100,
        maxQty: 500000,
        badge: 'HQ Real Likes | Instant Start',
        speed: 'Fast Delivery',
        description: 'Instant delivery likes for posts, reels, and carousels.'
      },
      {
        id: 3,
        categoryId: 1,
        categorySlug: 'instagram',
        name: 'Instagram Views',
        pricePerK: 15,
        minQty: 500,
        maxQty: 2000000,
        badge: 'Video & Reel Views | Super Fast',
        speed: 'Ultra Fast',
        description: 'High speed view delivery for reels and video posts.'
      },
      {
        id: 4,
        categoryId: 1,
        categorySlug: 'instagram',
        name: 'Instagram Comments',
        pricePerK: 50,
        minQty: 50,
        maxQty: 10000,
        badge: 'Custom Positive Comments | Active Profiles',
        speed: 'Moderate Speed',
        description: 'High quality relevant comments tailored to engagement algorithms.'
      },
      {
        id: 5,
        categoryId: 2,
        categorySlug: 'youtube',
        name: 'YouTube Views',
        pricePerK: 12,
        minQty: 1000,
        maxQty: 5000000,
        badge: 'Real Views • High Retention',
        speed: 'Fast Delivery',
        description: 'High retention organic view promotion for YouTube videos.'
      },
      {
        id: 6,
        categoryId: 2,
        categorySlug: 'youtube',
        name: 'YouTube Subscribers',
        pricePerK: 150,
        minQty: 100,
        maxQty: 50000,
        badge: 'Non-Drop Real Subscribers',
        speed: 'Gradual Delivery',
        description: 'Safe channel subscriber growth with organic profile appearance.'
      },
      {
        id: 7,
        categoryId: 3,
        categorySlug: 'telegram',
        name: 'Telegram Members',
        pricePerK: 45,
        minQty: 500,
        maxQty: 200000,
        badge: 'Real & Active Members • Instant Start',
        speed: 'Fast Delivery',
        description: 'Active channel and group member boosts with high stickiness.'
      },
      {
        id: 8,
        categoryId: 4,
        categorySlug: 'facebook',
        name: 'Facebook Page Likes & Followers',
        pricePerK: 40,
        minQty: 500,
        maxQty: 100000,
        badge: 'Real Global Profiles',
        speed: 'Fast Delivery',
        description: 'Boost public brand pages with real engagement metrics.'
      },
      {
        id: 9,
        categoryId: 5,
        categorySlug: 'tiktok',
        name: 'TikTok Followers',
        pricePerK: 38,
        minQty: 100,
        maxQty: 500000,
        badge: 'High Quality Accounts',
        speed: 'Fast Delivery',
        description: 'Accelerate algorithm reach with real profile following.'
      },
      {
        id: 10,
        categoryId: 6,
        categorySlug: 'twitter',
        name: 'Twitter (X) Followers',
        pricePerK: 55,
        minQty: 100,
        maxQty: 100000,
        badge: 'Global Active Profiles',
        speed: 'Fast Delivery',
        description: 'Clean and organic looking X followers for creators and brands.'
      }
    ],

    // User Orders matching reference
    orders: [
      {
        id: 1,
        code: '#10254',
        serviceName: 'Instagram Followers',
        categorySlug: 'instagram',
        quantity: 1000,
        price: 35,
        status: 'Processing',
        date: '12 May 2025, 4:32 PM',
        link: 'https://instagram.com/aarisali'
      },
      {
        id: 2,
        code: '#10253',
        serviceName: 'YouTube Views',
        categorySlug: 'youtube',
        quantity: 5000,
        price: 120,
        status: 'Completed',
        date: '11 May 2025, 6:10 PM',
        link: 'https://youtube.com/watch?v=sample123'
      },
      {
        id: 3,
        code: '#10252',
        serviceName: 'Telegram Members',
        categorySlug: 'telegram',
        quantity: 2000,
        price: 90,
        status: 'Processing',
        date: '10 May 2025, 1:45 PM',
        link: 'https://t.me/techchannel'
      },
      {
        id: 4,
        code: '#10251',
        serviceName: 'Instagram Likes',
        categorySlug: 'instagram',
        quantity: 1000,
        price: 20,
        status: 'Completed',
        date: '9 May 2025, 7:20 PM',
        link: 'https://instagram.com/p/Cxyz123'
      }
    ],

    // Transactions
    transactions: [
      {
        id: 4,
        code: 'TXN-1004',
        type: 'deposit',
        title: 'Add Funds',
        gateway: 'Razorpay',
        amount: 500,
        date: '12 May 2025, 4:12 PM',
        status: 'Completed'
      },
      {
        id: 3,
        code: 'TXN-1003',
        type: 'order',
        title: 'Order Payment',
        gateway: 'Instagram Followers',
        amount: 35,
        date: '12 May 2025, 4:32 PM',
        status: 'Completed'
      },
      {
        id: 2,
        code: 'TXN-1002',
        type: 'deposit',
        title: 'Add Funds',
        gateway: 'Razorpay',
        amount: 200,
        date: '10 May 2025, 11:20 AM',
        status: 'Completed'
      },
      {
        id: 1,
        code: 'TXN-1001',
        type: 'order',
        title: 'Order Payment',
        gateway: 'YouTube Views',
        amount: 120,
        date: '10 May 2025, 6:15 PM',
        status: 'Completed'
      }
    ],

    // Support Tickets
    tickets: [
      {
        id: 1,
        code: '#T1024',
        subject: 'Order not started yet',
        status: 'Open',
        date: '12 May 2025, 11:20 AM',
        messages: [
          { sender: 'user', name: 'Aaris Ali', text: 'Hello, my order #10254 has been processing for over 2 hours. Can you check?', time: '11:20 AM' }
        ]
      },
      {
        id: 2,
        code: '#T1023',
        subject: 'Payment issue',
        status: 'In Progress',
        date: '10 May 2025, 6:15 PM',
        messages: [
          { sender: 'user', name: 'Aaris Ali', text: 'I topped up ₹200 via UPI and waited for reflection.', time: '6:15 PM' },
          { sender: 'support', name: 'Support Team', text: 'Your transaction was verified and balance updated.', time: '6:30 PM' }
        ]
      },
      {
        id: 3,
        code: '#T1022',
        subject: 'Service delay',
        status: 'Closed',
        date: '8 May 2025, 3:40 PM',
        messages: [
          { sender: 'user', name: 'Aaris Ali', text: 'Instagram updates are causing slower delivery.', time: '3:40 PM' },
          { sender: 'support', name: 'Support Team', text: 'All orders have been speed boosted.', time: '4:00 PM' }
        ]
      },
      {
        id: 4,
        code: '#T1021',
        subject: 'Wrong quantity',
        status: 'Closed',
        date: '6 May 2025, 1:10 PM',
        messages: [
          { sender: 'user', name: 'Aaris Ali', text: 'I placed order with 1K quantity.', time: '1:10 PM' },
          { sender: 'support', name: 'Support Team', text: 'Refill completed as requested.', time: '1:30 PM' }
        ]
      }
    ],

    // New Order Form State
    newOrder: {
      step: 1,
      selectedCategory: 'instagram',
      selectedServiceId: 1,
      link: 'https://instagram.com/aarisali',
      quantity: 1000
    },

    // Add Funds State
    addFunds: {
      amount: 200,
      customAmount: '',
      method: 'Razorpay',
      isProcessing: false
    },

    // Filter states
    orderFilter: 'All',
    txnFilter: 'All',
    ticketFilter: 'All',
    serviceSearch: '',
    serviceCategoryFilter: '',

    // Support Modal & Detail State
    activeTicket: null,
    newTicket: { subject: '', message: '', priority: 'Medium' },
    ticketReplyText: '',
    showNewTicketModal: false,

    // Admin Panel State
    adminTab: 'dashboard',
    adminStats: {
      totalUsers: 142,
      totalOrders: 1258,
      pendingOrders: 18,
      completedOrders: 1210,
      revenue: 48920.00
    },
    adminUsers: [
      { id: 1024, name: 'Aaris Ali', email: 'aarisali@gmail.com', phone: '+91 98765 43210', balance: 850.50, orders: 4, status: 'active', joined: '10 May 2025' },
      { id: 1023, name: 'David Miller', email: 'david@sample.com', phone: '+1 555 123 4567', balance: 120.00, orders: 8, status: 'active', joined: '02 May 2025' },
      { id: 1022, name: 'Elena Rostova', email: 'elena@sample.org', phone: '+44 770 123 999', balance: 0.00, orders: 1, status: 'disabled', joined: '28 Apr 2025' },
      { id: 1021, name: 'Rajesh Sharma', email: 'rajesh@sample.in', phone: '+91 98111 22334', balance: 1450.00, orders: 25, status: 'active', joined: '15 Apr 2025' }
    ],
    selectedAdminUser: null,
    balanceAdjust: { amount: 100, operation: 'add', reason: 'Admin adjustment' },
    siteSettings: {
      siteName: 'SMM Panel',
      siteTagline: 'Grow Your Social Media',
      currency: '₹',
      currencyCode: 'INR',
      adminEmail: 'admin@smmpanel.local',
      supportEmail: 'support@smmpanel.local',
      minDeposit: 100
    },
    paymentSettings: {
      gatewayName: 'Razorpay',
      keyId: 'rzp_live_abc123456789',
      keySecret: '••••••••••••••••••••••••',
      isActive: true,
      minDeposit: 100,
      maxDeposit: 50000
    },
    newServiceModal: false,
    serviceForm: {
      id: null,
      categoryId: 1,
      name: '',
      pricePerK: 30,
      minQty: 100,
      maxQty: 1000000,
      badge: 'High Quality',
      speed: 'Fast Delivery',
      description: ''
    },

    // Auth Form State
    authForm: {
      email: 'aarisali@gmail.com',
      password: 'password123',
      name: 'Aaris Ali',
      phone: '+91 98765 43210',
      rememberMe: true
    },

    // Profile form state
    profileForm: {
      name: 'Aaris Ali',
      phone: '+91 98765 43210'
    },
    passwordForm: {
      current: '',
      new: '',
      confirm: ''
    },

    // Lifecycle Init
    init() {
      // Load saved state
      const savedUser = localStorage.getItem('smm_user');
      if (savedUser) {
        try {
          const parsed = JSON.parse(savedUser);
          this.user = { ...this.user, ...parsed };
        } catch (e) {}
      }

      const savedOrders = localStorage.getItem('smm_orders');
      if (savedOrders) {
        try { this.orders = JSON.parse(savedOrders); } catch (e) {}
      }

      const savedTxns = localStorage.getItem('smm_txns');
      if (savedTxns) {
        try { this.transactions = JSON.parse(savedTxns); } catch (e) {}
      }

      const savedTickets = localStorage.getItem('smm_tickets');
      if (savedTickets) {
        try { this.tickets = JSON.parse(savedTickets); } catch (e) {}
      }

      // Handle Initial Route from URL pathname
      this.handleRoute();

      // Listen for browser navigation (back/forward)
      window.addEventListener('popstate', () => {
        this.handleRoute();
      });

      // Try background syncing with live PHP backend
      this.syncWithBackend();
    },

    // Route Handler for Direct URL Access & Refresh
    handleRoute() {
      const path = (window.location.pathname || '').replace(/\/+$/, '') || '/';

      if (path === '/login') {
        this.currentView = 'auth';
        this.authTab = 'login';
      } else if (path === '/register') {
        this.currentView = 'auth';
        this.authTab = 'register';
      } else if (path === '/admin') {
        this.currentView = 'admin';
        this.adminTab = 'dashboard';
      } else if (path === '/dashboard') {
        this.currentView = 'dashboard';
      } else if (path === '/new-order') {
        this.currentView = 'new-order';
      } else if (path === '/services') {
        this.currentView = 'services';
      } else if (path === '/orders' || path === '/my-orders') {
        this.currentView = 'my-orders';
      } else if (path === '/wallet' || path === '/add-funds') {
        this.currentView = 'add-funds';
      } else if (path === '/transactions') {
        this.currentView = 'transactions';
      } else if (path === '/support') {
        this.currentView = 'support';
      } else if (path === '/profile') {
        this.currentView = 'profile';
      } else {
        this.currentView = 'landing';
      }

      this.$nextTick(() => {
        if (this.currentView === 'landing') {
          this.initLandingSlider();
        } else if (this.currentView === 'dashboard') {
          this.initDashboardSlider();
        }
      });
    },

    navigate(view, push = true) {
      this.currentView = view;
      window.scrollTo({ top: 0, behavior: 'smooth' });

      let path = '/';
      if (view === 'auth') {
        path = this.authTab === 'register' ? '/register' : '/login';
      } else if (view === 'admin') {
        path = '/admin';
      } else if (view === 'dashboard') {
        path = '/dashboard';
      } else if (view === 'new-order') {
        path = '/new-order';
      } else if (view === 'services') {
        path = '/services';
      } else if (view === 'my-orders') {
        path = '/orders';
      } else if (view === 'add-funds') {
        path = '/wallet';
      } else if (view === 'transactions') {
        path = '/transactions';
      } else if (view === 'support') {
        path = '/support';
      } else if (view === 'profile') {
        path = '/profile';
      } else {
        path = '/';
      }

      if (push && window.location.pathname !== path) {
        window.history.pushState({ view }, '', path);
      }

      this.$nextTick(() => {
        if (view === 'landing') {
          this.initLandingSlider();
        } else if (view === 'dashboard') {
          this.initDashboardSlider();
        }
      });
    },

    initLandingSlider() {
      setTimeout(() => {
        this.landingSliderInstance = init3DCardSlider({
          containerSelector: '#landing-slider-container',
          cardsSelector: '.landing-3d-card',
          initialIndex: 1,
          onIndexChange: (idx) => {
            this.activeLandingDot = idx;
          }
        });
      }, 80);
    },

    initDashboardSlider() {
      setTimeout(() => {
        this.dashboardSliderInstance = init3DCardSlider({
          containerSelector: '#dashboard-slider-container',
          cardsSelector: '.dashboard-3d-card',
          initialIndex: 1
        });
      }, 80);
    },

    jumpToLandingCard(index) {
      if (this.landingSliderInstance) {
        this.landingSliderInstance.goTo(index);
        this.activeLandingDot = index;
      }
    },

    orderFeaturedService(card) {
      if (card && card.serviceId) {
        this.newOrder.selectedServiceId = card.serviceId;
        const s = this.services.find(srv => srv.id === card.serviceId);
        if (s) {
          this.newOrder.selectedCategory = s.categorySlug;
          this.newOrder.quantity = s.minQty;
        }
      }
      this.navigate('new-order');
    },

    // Toast Notification Helper
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => {
        this.toast.show = false;
      }, 3500);
    },

    // Order Calculation & Placement
    getSelectedService() {
      return this.services.find(s => s.id === this.newOrder.selectedServiceId) || this.services[0];
    },

    calculateTotalPrice() {
      const service = this.getSelectedService();
      if (!service) return 0;
      const rate = (service.pricePerK / 1000) * this.newOrder.quantity;
      return Math.round(rate * 100) / 100;
    },

    incrementQty(amount = 500) {
      const service = this.getSelectedService();
      const max = service ? service.maxQty : 1000000;
      this.newOrder.quantity = Math.min(max, this.newOrder.quantity + amount);
    },

    decrementQty(amount = 500) {
      const service = this.getSelectedService();
      const min = service ? service.minQty : 100;
      this.newOrder.quantity = Math.max(min, this.newOrder.quantity - amount);
    },

    selectService(service) {
      this.newOrder.selectedServiceId = service.id;
      this.newOrder.selectedCategory = service.categorySlug;
      this.newOrder.quantity = service.minQty;
      this.newOrder.step = 2;
    },

    placeOrder() {
      const service = this.getSelectedService();
      const price = this.calculateTotalPrice();

      if (!this.newOrder.link || !this.newOrder.link.includes('http')) {
        this.showToast('Please enter a valid social media URL.', 'error');
        return;
      }

      if (this.user.balance < price) {
        this.showToast(`Insufficient balance (₹${this.user.balance}). Please add funds to place order.`, 'error');
        this.navigate('add-funds');
        return;
      }

      // Deduct balance
      this.user.balance = Math.round((this.user.balance - price) * 100) / 100;

      const orderCode = '#' + (10255 + this.orders.length);
      const newOrderObj = {
        id: this.orders.length + 1,
        code: orderCode,
        serviceName: service.name,
        categorySlug: service.categorySlug,
        quantity: this.newOrder.quantity,
        price: price,
        status: 'Processing',
        date: 'Just now',
        link: this.newOrder.link
      };

      this.orders.unshift(newOrderObj);

      // Add transaction
      const txnCode = 'TXN-' + (1005 + this.transactions.length);
      this.transactions.unshift({
        id: this.transactions.length + 1,
        code: txnCode,
        type: 'order',
        title: 'Order Payment',
        gateway: service.name,
        amount: price,
        date: 'Just now',
        status: 'Completed'
      });

      this.persistData();
      this.showToast(`Order ${orderCode} placed successfully!`);
      this.navigate('my-orders');
    },

    // Wallet & Add Funds
    setAddFundsPreset(val) {
      this.addFunds.amount = val;
      this.addFunds.customAmount = '';
    },

    processDeposit() {
      const amount = this.addFunds.customAmount ? parseFloat(this.addFunds.customAmount) : this.addFunds.amount;
      if (!amount || amount < 100) {
        this.showToast('Minimum deposit amount is ₹100.', 'error');
        return;
      }

      this.addFunds.isProcessing = true;
      setTimeout(() => {
        this.user.balance = Math.round((this.user.balance + amount) * 100) / 100;
        const txnCode = 'TXN-' + (1005 + this.transactions.length);
        this.transactions.unshift({
          id: this.transactions.length + 1,
          code: txnCode,
          type: 'deposit',
          title: 'Add Funds',
          gateway: 'Razorpay',
          amount: amount,
          date: 'Just now',
          status: 'Completed'
        });

        this.persistData();
        this.addFunds.isProcessing = false;
        this.showToast(`₹${amount} added successfully to your wallet!`);
        this.navigate('dashboard');
      }, 800);
    },

    // Support Tickets
    createTicket() {
      if (!this.newTicket.subject || !this.newTicket.message) {
        this.showToast('Please fill in both subject and message.', 'error');
        return;
      }

      const ticketCode = '#T' + (1025 + this.tickets.length);
      const ticket = {
        id: this.tickets.length + 1,
        code: ticketCode,
        subject: this.newTicket.subject,
        status: 'Open',
        date: 'Just now',
        messages: [
          { sender: 'user', name: this.user.name, text: this.newTicket.message, time: 'Just now' }
        ]
      };

      this.tickets.unshift(ticket);
      this.newTicket = { subject: '', message: '', priority: 'Medium' };
      this.showNewTicketModal = false;
      this.persistData();
      this.showToast(`Ticket ${ticketCode} submitted! Our team will respond shortly.`);
    },

    openTicket(ticket) {
      this.activeTicket = ticket;
    },

    sendTicketReply() {
      if (!this.ticketReplyText.trim() || !this.activeTicket) return;
      this.activeTicket.messages.push({
        sender: 'user',
        name: this.user.name,
        text: this.ticketReplyText.trim(),
        time: 'Just now'
      });
      this.ticketReplyText = '';
      this.persistData();
      this.showToast('Reply submitted.');
    },

    // Profile Updates
    updateProfile() {
      if (!this.profileForm.name) {
        this.showToast('Name cannot be empty.', 'error');
        return;
      }
      this.user.name = this.profileForm.name;
      this.user.phone = this.profileForm.phone;
      this.persistData();
      this.showToast('Profile updated successfully!');
    },

    updatePassword() {
      if (!this.passwordForm.current || !this.passwordForm.new) {
        this.showToast('Please fill in all password fields.', 'error');
        return;
      }
      if (this.passwordForm.new !== this.passwordForm.confirm) {
        this.showToast('New passwords do not match.', 'error');
        return;
      }
      this.passwordForm = { current: '', new: '', confirm: '' };
      this.showToast('Password changed successfully!');
    },

    // Auth Login & Register
    loginUser() {
      if (!this.authForm.email || !this.authForm.password) {
        this.showToast('Please enter both email and password.', 'error');
        return;
      }

      // Check admin login
      if (this.authForm.email === 'admin@smmpanel.local') {
        this.user = {
          id: 1,
          name: 'Administrator',
          email: 'admin@smmpanel.local',
          phone: '+91 99999 99999',
          balance: 0.00,
          role: 'admin',
          joined: '01 Jan 2025',
          isLoggedIn: true
        };
        this.showToast('Logged in as Administrator.');
        this.navigate('admin');
        return;
      }

      this.user.isLoggedIn = true;
      this.user.email = this.authForm.email;
      this.persistData();
      this.showToast('Welcome back, ' + this.user.name + '!');
      this.navigate('dashboard');
    },

    registerUser() {
      if (!this.authForm.name || !this.authForm.email || !this.authForm.password) {
        this.showToast('Please fill in name, email, and password.', 'error');
        return;
      }

      this.user = {
        id: 1025 + Math.floor(Math.random() * 50),
        name: this.authForm.name,
        email: this.authForm.email,
        phone: this.authForm.phone || '+91 98765 00000',
        balance: 0.00,
        role: 'user',
        joined: 'Today',
        isLoggedIn: true
      };
      this.persistData();
      this.showToast('Account created successfully! Welcome.');
      this.navigate('dashboard');
    },

    logout() {
      this.user.isLoggedIn = false;
      localStorage.removeItem('smm_user');
      this.showToast('Logged out successfully.');
      this.navigate('landing');
    },

    // Admin Operations
    updateOrderStatus(order, newStatus) {
      order.status = newStatus;
      if (newStatus === 'Cancelled') {
        this.user.balance = Math.round((this.user.balance + order.price) * 100) / 100;
        this.transactions.unshift({
          id: this.transactions.length + 1,
          code: 'TXN-' + (1005 + this.transactions.length),
          type: 'refund',
          title: 'Order Refund',
          gateway: 'System',
          amount: order.price,
          date: 'Just now',
          status: 'Completed'
        });
      }
      this.persistData();
      this.showToast(`Order ${order.code} updated to ${newStatus}.`);
    },

    openAdjustBalance(u) {
      this.selectedAdminUser = u;
      this.balanceAdjust = { amount: 100, operation: 'add', reason: 'Admin adjustment' };
    },

    submitBalanceAdjust() {
      if (!this.selectedAdminUser || this.balanceAdjust.amount <= 0) return;
      const amt = parseFloat(this.balanceAdjust.amount);
      if (this.balanceAdjust.operation === 'add') {
        this.selectedAdminUser.balance += amt;
      } else {
        this.selectedAdminUser.balance = Math.max(0, this.selectedAdminUser.balance - amt);
      }
      if (this.selectedAdminUser.id === this.user.id) {
        this.user.balance = this.selectedAdminUser.balance;
      }
      this.persistData();
      this.showToast(`Updated balance for ${this.selectedAdminUser.name}.`);
      this.selectedAdminUser = null;
    },

    toggleUserStatus(u) {
      u.status = u.status === 'active' ? 'disabled' : 'active';
      this.showToast(`User ${u.name} status is now ${u.status}.`);
      this.persistData();
    },

    openAddService() {
      this.serviceForm = {
        id: null,
        categoryId: 1,
        name: '',
        pricePerK: 30,
        minQty: 100,
        maxQty: 1000000,
        badge: 'High Quality',
        speed: 'Fast Delivery',
        description: ''
      };
      this.newServiceModal = true;
    },

    saveService() {
      if (!this.serviceForm.name || !this.serviceForm.pricePerK) {
        this.showToast('Please fill in service name and price.', 'error');
        return;
      }

      const cat = this.categories.find(c => c.id === parseInt(this.serviceForm.categoryId)) || this.categories[0];

      if (this.serviceForm.id) {
        const idx = this.services.findIndex(s => s.id === this.serviceForm.id);
        if (idx !== -1) {
          this.services[idx] = {
            ...this.services[idx],
            name: this.serviceForm.name,
            categoryId: parseInt(this.serviceForm.categoryId),
            categorySlug: cat.slug,
            pricePerK: parseFloat(this.serviceForm.pricePerK),
            minQty: parseInt(this.serviceForm.minQty),
            maxQty: parseInt(this.serviceForm.maxQty),
            badge: this.serviceForm.badge,
            speed: this.serviceForm.speed,
            description: this.serviceForm.description
          };
        }
        this.showToast('Service updated successfully.');
      } else {
        const newServ = {
          id: this.services.length + 1,
          categoryId: parseInt(this.serviceForm.categoryId),
          categorySlug: cat.slug,
          name: this.serviceForm.name,
          pricePerK: parseFloat(this.serviceForm.pricePerK),
          minQty: parseInt(this.serviceForm.minQty),
          maxQty: parseInt(this.serviceForm.maxQty),
          badge: this.serviceForm.badge,
          speed: this.serviceForm.speed,
          description: this.serviceForm.description
        };
        this.services.push(newServ);
        this.showToast('Service created successfully.');
      }

      this.newServiceModal = false;
      this.persistData();
    },

    deleteService(serviceId) {
      if (confirm('Are you sure you want to delete this service?')) {
        this.services = this.services.filter(s => s.id !== serviceId);
        this.showToast('Service deleted.');
        this.persistData();
      }
    },

    saveSettings() {
      this.showToast('System settings saved successfully.');
    },

    savePaymentSettings() {
      this.showToast('Payment gateway settings updated.');
    },

    // Persistence & Backend Sync Helper
    persistData() {
      localStorage.setItem('smm_user', JSON.stringify(this.user));
      localStorage.setItem('smm_orders', JSON.stringify(this.orders));
      localStorage.setItem('smm_txns', JSON.stringify(this.transactions));
      localStorage.setItem('smm_tickets', JSON.stringify(this.tickets));
    },

    async syncWithBackend() {
      try {
        const res = await fetch('/api/auth.php?action=session');
        if (res.ok) {
          const data = await res.json();
          if (data && data.success && data.user) {
            this.user = { ...this.user, ...data.user, isLoggedIn: true };
          }
        }
      } catch (e) {}
    },

    filteredOrders() {
      if (this.orderFilter === 'All') return this.orders;
      return this.orders.filter(o => o.status === this.orderFilter);
    },

    filteredTransactions() {
      if (this.txnFilter === 'All') return this.transactions;
      if (this.txnFilter === 'Add Funds') return this.transactions.filter(t => t.type === 'deposit');
      if (this.txnFilter === 'Orders') return this.transactions.filter(t => t.type === 'order');
      if (this.txnFilter === 'Refunds') return this.transactions.filter(t => t.type === 'refund');
      return this.transactions;
    },

    filteredTickets() {
      if (this.ticketFilter === 'All') return this.tickets;
      return this.tickets.filter(t => t.status === this.ticketFilter);
    },

    filteredServices() {
      return this.services.filter(s => {
        const matchesCategory = !this.serviceCategoryFilter || s.categorySlug === this.serviceCategoryFilter;
        const matchesSearch = !this.serviceSearch || s.name.toLowerCase().includes(this.serviceSearch.toLowerCase());
        return matchesCategory && matchesSearch;
      });
    }
  };
};

// Start Alpine
Alpine.start();

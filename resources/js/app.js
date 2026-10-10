import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(collapse);

/**
 * Theme store — persisted in localStorage, applied before paint by the
 * inline script in the root layout to avoid a flash of the wrong theme.
 */
Alpine.store('theme', {
    current: 'light',

    init() {
        const saved = localStorage.getItem('madarek_theme');
        this.current = saved || 'light';
    },

    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('madarek_theme', this.current);
        document.documentElement.classList.toggle('dark', this.current === 'dark');
    },

    isDark() {
        return this.current === 'dark';
    },
});

Alpine.data('compass', (questions, recommendations) => ({
    questions,
    recommendations,
    step: 0,
    answers: {},
    done: false,

    get total() {
        return this.questions.length;
    },

    get question() {
        return this.questions[this.step] ?? null;
    },

    get isLast() {
        return this.step === this.total - 1;
    },

    get answeredCount() {
        return Object.keys(this.answers).length;
    },

    get answered() {
        return Boolean(this.question && this.answers[this.question.id]);
    },

    get progress() {
        return this.done ? 100 : (this.answeredCount / Math.max(this.total, 1)) * 100;
    },

    get topTags() {
        const tally = {};

        for (const question of this.questions) {
            const picked = this.answers[question.id];
            if (!picked) continue;

            const option = question.options.find((o) => o.id === picked);
            if (option?.tag) tally[option.tag] = (tally[option.tag] ?? 0) + 1;
        }

        return Object.entries(tally)
            .sort((a, b) => b[1] - a[1])
            .map(([tag]) => tag);
    },

    get matched() {
        return this.recommendations
            .map((journey) => ({
                ...journey,
                score: this.topTags.some((tag) => journey.title.includes(tag)) ? 1 : 0,
            }))
            .sort((a, b) => b.score - a.score)
            .slice(0, 3);
    },

    select(questionId, optionId) {
        this.answers[questionId] = optionId;
    },

    isSelected(questionId, optionId) {
        return this.answers[questionId] === optionId;
    },

    next() {
        if (!this.answered) return;

        this.isLast ? (this.done = true) : this.step++;
    },

    back() {
        this.step = Math.max(0, this.step - 1);
    },

    restart() {
        this.step = 0;
        this.answers = {};
        this.done = false;
    },
}));

/** Modal / drawer open-close used by the upload dialog and mobile nav. */
Alpine.data('overlay', () => ({
    open: false,

    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },

    hide() {
        this.open = false;
        document.body.style.overflow = '';
    },

    toggle() {
        this.open ? this.hide() : this.show();
    },
}));

Alpine.start();
import './bootstrap';
import 'bootstrap';

const jsonHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
};

(() => {
    const root = document.querySelector('[data-order-tracking]');
    if (!root) {
        return;
    }

    const url = root.getAttribute('data-status-url');
    const labelEl = root.querySelector('[data-order-status-label]');
    const timelineEl = root.querySelector('[data-order-timeline]');

    if (!url || !labelEl || !timelineEl) {
        return;
    }

    let stopped = false;

    const applyPayload = (payload) => {
        if (payload.status_label) {
            labelEl.textContent = payload.status_label;
        }

        if (Array.isArray(payload.timeline)) {
            payload.timeline.forEach((step) => {
                const item = timelineEl.querySelector(`[data-step="${step.key}"]`);
                if (!item) {
                    return;
                }

                item.classList.remove(
                    'order-timeline__step--done',
                    'order-timeline__step--current',
                    'order-timeline__step--upcoming',
                    'order-timeline__step--skipped',
                );
                item.classList.add(`order-timeline__step--${step.state}`);
            });
        }

        if (payload.is_terminal) {
            stopped = true;
        }
    };

    const poll = async () => {
        if (stopped) {
            return;
        }

        try {
            const response = await fetch(url, {
                headers: jsonHeaders,
                credentials: 'same-origin',
            });

            if (response.ok) {
                applyPayload(await response.json());
            }
        } catch {
            // Keep the last rendered server status if polling fails.
        }
    };

    window.setInterval(poll, 15000);
})();

(() => {
    const form = document.getElementById('checkout-form');
    if (!form) {
        return;
    }

    const previewUrl = form.getAttribute('data-checkout-preview-url');
    const deliveryFeeEl = form.querySelector('[data-checkout-delivery-fee]');
    const totalEl = form.querySelector('[data-checkout-total]');
    const noteEl = form.querySelector('[data-checkout-preview-note]');
    const zoneSelect = form.querySelector('#delivery_zone_id');
    const fulfillmentInputs = form.querySelectorAll('input[name="fulfillment_type"]');

    if (!previewUrl || !deliveryFeeEl || !totalEl) {
        return;
    }

    const formatPkr = (value) => `PKR ${Number(value).toLocaleString('en-PK')}`;

    const refreshPreview = async () => {
        const fulfillment = form.querySelector('input[name="fulfillment_type"]:checked')?.value || 'pickup';
        const params = new URLSearchParams({ fulfillment_type: fulfillment });

        if (fulfillment === 'delivery' && zoneSelect?.value) {
            params.set('delivery_zone_id', zoneSelect.value);
        }

        if (fulfillment === 'delivery' && !zoneSelect?.value) {
            deliveryFeeEl.textContent = 'Select a zone';
            noteEl && (noteEl.textContent = 'Choose a delivery zone to preview the fee from the server.');
            return;
        }

        try {
            const response = await fetch(`${previewUrl}?${params.toString()}`, {
                headers: jsonHeaders,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const cart = payload.data?.cart;
            if (!cart) {
                return;
            }

            deliveryFeeEl.textContent = formatPkr(cart.delivery_fee_pkr);
            totalEl.textContent = formatPkr(cart.total_pkr);

            if (noteEl) {
                noteEl.textContent = payload.data.ordering_open
                    ? 'Preview totals are server-priced. Final total is recalculated on submit.'
                    : (payload.data.ordering_message || 'Ordering is currently unavailable.');
            }
        } catch {
            // Keep last rendered totals.
        }
    };

    fulfillmentInputs.forEach((input) => input.addEventListener('change', refreshPreview));
    zoneSelect?.addEventListener('change', refreshPreview);
})();

(() => {
    const inbox = document.querySelector('[data-order-inbox]');
    if (!inbox) {
        return;
    }

    const url = inbox.getAttribute('data-inbox-url');
    const countEl = document.querySelector('[data-order-inbox-count]');
    const stampEl = document.querySelector('[data-order-inbox-stamp]');

    if (!url || !countEl) {
        return;
    }

    const poll = async () => {
        try {
            const response = await fetch(url, {
                headers: jsonHeaders,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            countEl.textContent = String(payload.meta?.count ?? payload.data?.length ?? 0);
            if (stampEl && payload.meta?.generated_at) {
                stampEl.textContent = `· refreshed ${new Date(payload.meta.generated_at).toLocaleTimeString()}`;
            }
        } catch {
            // Keep last count.
        }
    };

    window.setInterval(poll, 20000);
})();

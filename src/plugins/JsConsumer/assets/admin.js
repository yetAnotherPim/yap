const CONSUME_DELAY = 5_000;
const CONSUME_URL = '/admin/js-consumer/consume';

async function consumeMessage() {
    try {
        const response = await fetch(CONSUME_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
            },
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            console.error(
                'JS Consumer:',
                result.errorMessage ?? `HTTP ${response.status}`,
            );
        }
    } catch (error) {
        console.error('JS Consumer:', error);
    } finally {
        setTimeout(consumeMessage, CONSUME_DELAY);
    }
}

consumeMessage();

export class Translator {
    private translations: { [key: string]: string };
    private readonly cacheTTL: number;
    private readonly translationURL: string = '/trans';

    constructor(cacheTTL: number = 3600000) {
        this.translations = JSON.parse(localStorage.getItem('translations') || '{}');
        this.cacheTTL = cacheTTL;
    }

    private provideTranslation = async (key: string): Promise<string> => {
        const cacheKey: string = `trans_${key}`;
        const cacheTTLKey: string = `trans_ttl_${key}`;

        const cachedTranslation: null|string = localStorage.getItem(cacheKey);
        const cachedTimestamp: number = localStorage.getItem(cacheTTLKey) as unknown as number;

        if (null !== cachedTranslation && cachedTimestamp && (Date.now() - cachedTimestamp) < this.cacheTTL) {
            return cachedTranslation;
        }

        try {
            const response: Response = await fetch(`${this.translationURL}/${key}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const translation = await response.json();

                localStorage.setItem(cacheKey, translation);
                localStorage.setItem(cacheTTLKey, Date.now().toString());

                return translation;
            } else {
                console.error('The error occurred while fetching the translation.');
            }
        } catch (error) {
            console.error('The error occurred while fetching the translation.');
        }

        return key;
    }

    public trans = async (key: string): Promise<string> => {
        if (typeof this.translations[key] !== 'undefined') {
            return this.translations[key];
        }

        return await this.provideTranslation(key);
    }

    public fetchTranslations = async (force: boolean = false): Promise<void> => {
        if (!force && Object.keys(this.translations).length) {
            return;
        }

        try {
            const response: Response = await fetch(`${this.translationURL}/all`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                this.translations = await response.json();
                localStorage.setItem('translations', JSON.stringify(this.translations));
            } else {
                console.error('The error occurred while fetching the translation.');
            }
        } catch (error) {
            console.error('The error occurred while fetching the translation.');
        }
    }
}

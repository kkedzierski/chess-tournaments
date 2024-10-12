import './styles/main.css';
import { Toast } from "./Toaster";
import { Translator } from "./Translator";

document.addEventListener('DOMContentLoaded', async () => {
    const translator = new Translator();
    await translator.fetchTranslations();
    const tinField = document.querySelector('#Company_taxIdentificationNumber');

    if (tinField) {
        tinField.addEventListener('change', async function() {
            const tin = this.value;

            if (tin.length === 10) {
                Toast.success(await translator.trans('dashboard.panel.gusApi.pending.description'));
                try {
                    const response = await fetch(`/dashboard/company/fetch-gus-data`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ tin: tin }),
                    });

                    if (response.ok) {
                        const data = await response.json();

                        document.querySelector('input[name="Company[name]"]').value = data.name || '';
                        document.querySelector('input[name="Company[province]"]').value = data.province || '';
                        document.querySelector('input[name="Company[city]"]').value = data.city || '';
                        document.querySelector('input[name="Company[zipCode]"]').value = data.zipCode || '';
                        document.querySelector('input[name="Company[street]"]').value = data.street || '';
                    } else {
                        Toast.error(await translator.trans('exception.fetchingGusData'));
                    }
                } catch (error) {
                    Toast.error(await translator.trans('exception.fetchingGusData'));
                }
            } else {
                Toast.error(await translator.trans('exception.invalidGusTin'));
            }
        });
    }
});
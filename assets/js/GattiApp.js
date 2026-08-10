/*
 * Componente React della pagina Ospiti
 * Recupera i gatti dal backend PHP e gestisce ricerca, ordinamento e selezione
 * La selezione viene comunicata al form Vanilla JavaScript tramite CustomEvent
 */

const { useState, useEffect } = React;

function GattiApp() {
    const [gatti, setGatti] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errore, setErrore] = useState(null);
    const [ricercaTesto, setRicercaTesto] = useState('');
    const [criterioOrdinamento, setCriterioOrdinamento] = useState('data_desc');
    const [gattiSelezionati, setGattiSelezionati] = useState([]);

    /*
     * Al primo caricamento il componente interroga asincronamente il backend
     * L'API restituisce un oggetto JSON contenente lo stato e l'array dei gatti
     */
    useEffect(() => {
        fetch('actions/get_gatti.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore nella risposta del server');
                }

                return response.json();
            })
            .then(result => {
                if (result.status === 'success') {
                    setGatti(result.data);
                } else {
                    setErrore(result.message);
                }

                setLoading(false);
            })
            .catch(() => {
                setErrore('Errore di connessione al server.');
                setLoading(false);
            });
    }, []);

    /*
     * Ogni variazione della selezione genera l'evento richiesto dalla consegna
     * L'array dei gatti scelti viene inserito nella proprietà detail dell'evento
     */
    useEffect(() => {
        const evento = new CustomEvent('aggiornamentoGattiScelti', {
            detail: gattiSelezionati
        });

        document.dispatchEvent(evento);
    }, [gattiSelezionati]);

    // Solo un utente autenticato può aggiungere o rimuovere un gatto dalla selezione
    const gestisciSelezione = gatto => {
        if (!IS_LOGGED_IN) {
            return;
        }

        setGattiSelezionati(selezionatiPrecedenti => {
            const giaPresente = selezionatiPrecedenti.some(g => g.id === gatto.id);

            if (giaPresente) {
                return selezionatiPrecedenti.filter(g => g.id !== gatto.id);
            }

            return [...selezionatiPrecedenti, gatto];
        });
    };

    /*
     * La ricerca libera viene applicata sia al nome sia alla descrizione
     * L'uso delle stringhe vuote evita errori nel caso di valori mancanti
     */
    const gattiVisualizzati = gatti.filter(gatto => {
        const testo = ricercaTesto.toLowerCase();
        const nome = gatto.nome ? gatto.nome.toLowerCase() : '';
        const descrizione = gatto.descrizione ? gatto.descrizione.toLowerCase() : '';

        return nome.includes(testo) || descrizione.includes(testo);
    });

    // L'ordinamento viene applicato all'array già filtrato in base al criterio selezionato
    gattiVisualizzati.sort((a, b) => {
        if (criterioOrdinamento === 'eta_asc') {
            return a.eta - b.eta;
        }

        if (criterioOrdinamento === 'eta_desc') {
            return b.eta - a.eta;
        }

        if (criterioOrdinamento === 'data_desc') {
            return new Date(b.data_arrivo) - new Date(a.data_arrivo);
        }

        if (criterioOrdinamento === 'colore_asc') {
            return (a.colore_mantello || '').localeCompare(b.colore_mantello || '');
        }

        return 0;
    });

    if (loading) {
        return <div className="messaggio-di-stato">Caricamento ospiti in corso...</div>;
    }

    if (errore) {
        return <div className="messaggio-errore">{errore}</div>;
    }

    return (
        <div className="react-container">
            <div className="filtri-ricerca">
                <input type="text" placeholder="Cerca per nome o descrizione..." value={ricercaTesto} onChange={event => setRicercaTesto(event.target.value)} className="input-ricerca" />

                <select value={criterioOrdinamento} onChange={event => setCriterioOrdinamento(event.target.value)} className="select-ordinamento">
                    <option value="data_desc">Ordina per: Ultimi Arrivi</option>
                    <option value="eta_asc">Ordina per: I più cuccioli (Età crescente)</option>
                    <option value="eta_desc">Ordina per: I più maturi (Età decrescente)</option>
                    <option value="colore_asc">Ordina per: Colore Pelo (A-Z)</option>
                </select>
            </div>

            {gattiVisualizzati.length === 0 ? (
                <p className="nessun-risultato">Nessun felino corrisponde ai criteri di ricerca.</p>
            ) : (
                <div className="gatti-grid-2">
                    {gattiVisualizzati.map(gatto => {
                        const isSelezionato = gattiSelezionati.some(g => g.id === gatto.id);

                        let classiCard = 'card-gatto-premium';

                        if (IS_LOGGED_IN) {
                            classiCard += ' card-cliccabile';
                        }

                        if (isSelezionato) {
                            classiCard += ' selezionata';
                        }

                        return (
                            <article key={gatto.id} className={classiCard} onClick={() => gestisciSelezione(gatto)}>
                                <div className="card-img-wrapper">
                                    <span className="badge-nuovo">{gatto.sesso === 'M' ? 'Maschio' : 'Femmina'}</span>
                                    <img src="assets/img/placeholder_gatto.png" alt={'Foto di ' + gatto.nome} />
                                </div>

                                <div className="card-body">
                                    <h3>{gatto.nome}</h3>
                                    <p className="card-desc">
                                        <strong>Razza: </strong> {gatto.razza} <br/>
                                        <strong>Manto: </strong> {gatto.colore_mantello} <br/>
                                        <strong>Età: </strong> {gatto.eta} anni <br/>
                                        <strong>Arrivo: </strong> {new Date(gatto.data_arrivo).toLocaleDateString('it-IT')} <br/><br/>
                                        {gatto.descrizione}
                                    </p>
                                </div>
                            </article>
                        );
                    })}
                </div>
            )}
        </div>
    );
}

const root = ReactDOM.createRoot(document.getElementById('react-root'));
root.render(<GattiApp />);
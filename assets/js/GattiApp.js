/*
 * Componente React della pagina Ospiti
 * Recupera i gatti dal backend PHP e gestisce ricerca, ordinamento e selezione
 * La selezione viene comunicata al form Vanilla JavaScript tramite CustomEvent
 */

// Gli hook utilizzati dal componente vengono recuperati dall'oggetto React
const { useState, useEffect } = React;

function GattiApp() {
    /*
     * Gli stati mantengono i dati che cambiano durante l'utilizzo della pagina:
     * elenco dei gatti, stato della richiesta, filtri e selezione corrente
     */
    const [gatti, setGatti] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errore, setErrore] = useState(null);
    const [ricercaTesto, setRicercaTesto] = useState('');
    const [criterioOrdinamento, setCriterioOrdinamento] = useState('data_desc');
    const [gattiSelezionati, setGattiSelezionati] = useState([]);

    /*
     * Al primo caricamento React interroga asincronamente il backend
     * L'array delle dipendenze vuoto fa eseguire l'effetto una sola volta al montaggio
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
                // Lo stato applicativo restituito dall'API viene controllato oltre alla risposta HTTP
                if (result.status === 'success') {
                    setGatti(result.data);
                } else {
                    setErrore(result.message);
                }

                setLoading(false);
            })
            .catch(() => {
                // In caso di errore di rete viene mostrato un messaggio comprensibile all'utente
                setErrore('Errore di connessione al server.');
                setLoading(false);
            });
    }, []);

    /*
     * Quando cambia la selezione viene emesso il CustomEvent richiesto dalla consegna
     * detail trasporta al Vanilla JavaScript di ospiti.php l'array dei gatti scelti
     */
    useEffect(() => {
        const evento = new CustomEvent('aggiornamentoGattiScelti', {
            detail: gattiSelezionati
        });

        // L'evento viene emesso sul document perché il listener è definito in ospiti.php
        document.dispatchEvent(evento);
    }, [gattiSelezionati]);

    /*
     * Gestisce sia la selezione sia la deselezione di un gatto
     * IS_LOGGED_IN viene definito in ospiti.php in base alla sessione PHP
     */
    const gestisciSelezione = gatto => {
        if (!IS_LOGGED_IN) {
            return;
        }

        setGattiSelezionati(selezionatiPrecedenti => {
            const giaPresente = selezionatiPrecedenti.some(g => g.id === gatto.id);

            if (giaPresente) {
                return selezionatiPrecedenti.filter(g => g.id !== gatto.id);
            }

            // Viene creato un nuovo array senza modificare direttamente lo stato precedente
            return [...selezionatiPrecedenti, gatto];
        });
    };

    // Enter e Spazio permettono agli utenti autenticati di selezionare le card anche tramite tastiera
    const gestisciTastiera = (event, gatto) => {
        if (!IS_LOGGED_IN) {
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            // Evita in particolare lo scorrimento della pagina normalmente associato alla barra spaziatrice
            event.preventDefault();
            gestisciSelezione(gatto);
        }
    };

    /*
     * La ricerca viene applicata sia al nome sia alla descrizione
     * Le stringhe vuote evitano errori nel caso di eventuali valori mancanti
     */
    const gattiVisualizzati = gatti.filter(gatto => {
        const testo = ricercaTesto.toLowerCase();
        const nome = gatto.nome ? gatto.nome.toLowerCase() : '';
        const descrizione = gatto.descrizione ? gatto.descrizione.toLowerCase() : '';

        return nome.includes(testo) || descrizione.includes(testo);
    });

    /*
     * L'ordinamento viene applicato all'array già prodotto da filter
     * Lo stato originale gatti non viene quindi ordinato direttamente
     */
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

    // Durante la richiesta la galleria viene temporaneamente sostituita dal messaggio di caricamento
    if (loading) {
        return <div className="messaggio-di-stato">Caricamento ospiti in corso...</div>;
    }

    // Se il caricamento non riesce viene mostrato il messaggio memorizzato nello stato errore
    if (errore) {
        return <div className="messaggio-errore">{errore}</div>;
    }

    return (
        <div className="react-container">

            {/* I controlli aggiornano gli stati utilizzati per filtrare e ordinare i dati già caricati */}
            <div className="filtri-ricerca">
                <input
                    type="text"
                    placeholder="Cerca per nome o descrizione..."
                    value={ricercaTesto}
                    onChange={event => setRicercaTesto(event.target.value)}
                    className="input-ricerca"
                    aria-label="Cerca gatti per nome o descrizione"
                />

                <select
                    value={criterioOrdinamento}
                    onChange={event => setCriterioOrdinamento(event.target.value)}
                    className="select-ordinamento"
                    aria-label="Ordina i gatti"
                >
                    <option value="data_desc">Ordina per: Ultimi Arrivi</option>
                    <option value="eta_asc">Ordina per: I più cuccioli (Età crescente)</option>
                    <option value="eta_desc">Ordina per: I più maturi (Età decrescente)</option>
                    <option value="colore_asc">Ordina per: Colore Pelo (A-Z)</option>
                </select>
            </div>

            {/* Se il filtro non produce risultati viene mostrato un messaggio al posto della griglia */}
            {gattiVisualizzati.length === 0 ? (
                <p className="nessun-risultato">Nessun felino corrisponde ai criteri di ricerca.</p>
            ) : (
                <div className="gatti-grid-2">

                    {/* map genera una card per ogni gatto utilizzando l'ID del database come key univoca */}
                    {gattiVisualizzati.map(gatto => {
                        const isSelezionato = gattiSelezionati.some(g => g.id === gatto.id);

                        // let è utilizzato perché le classi vengono aggiunte in base allo stato della card
                        let classiCard = 'card-gatto-premium';

                        if (IS_LOGGED_IN) {
                            classiCard += ' card-cliccabile';
                        }

                        if (isSelezionato) {
                            classiCard += ' selezionata';
                        }

                        return (
                            <article
                                key={gatto.id}
                                className={classiCard}
                                onClick={IS_LOGGED_IN ? () => gestisciSelezione(gatto) : undefined}
                                onKeyDown={IS_LOGGED_IN ? event => gestisciTastiera(event, gatto) : undefined}
                                tabIndex={IS_LOGGED_IN ? 0 : undefined}
                            >
                                <div className="card-img-wrapper">

                                    {/* Il ternario traduce il codice del sesso nel testo mostrato sul badge */}
                                    <span className="badge-nuovo">
                                        {gatto.sesso === 'M' ? 'Maschio' : 'Femmina'}
                                    </span>

                                    {/* Il file è un placeholder generico e non una fotografia del singolo gatto */}
                                    <img src="assets/img/placeholder_gatto.png" alt="" />
                                </div>

                                <div className="card-body">

                                    {/* Il nome usa h2 perché la galleria dipende direttamente dal titolo principale della pagina */}
                                    <h2>{gatto.nome}</h2>

                                    <p className="card-desc">
                                        <strong>Razza: </strong> {gatto.razza} <br />
                                        <strong>Manto: </strong> {gatto.colore_mantello} <br />
                                        <strong>Età: </strong> {gatto.eta} anni <br />
                                        <strong>Arrivo: </strong> {new Date(gatto.data_arrivo).toLocaleDateString('it-IT')} <br /><br />
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

// Il componente viene montato nel contenitore react-root predisposto in ospiti.php
const root = ReactDOM.createRoot(document.getElementById('react-root'));
root.render(<GattiApp />);
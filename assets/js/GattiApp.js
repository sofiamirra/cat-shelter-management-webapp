const { useState, useEffect } = React;

function GattiApp() {
    const [gatti, setGatti] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errore, setErrore] = useState(null);
    const [ricercaTesto, setRicercaTesto] = useState("");
    const [criterioOrdinamento, setCriterioOrdinamento] = useState("data_desc");
    
    // NUOVO STATO: Array per memorizzare i gatti selezionati dall'utente
    const [gattiSelezionati, setGattiSelezionati] = useState([]);

    useEffect(() => {
        fetch('get_gatti.php')
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    setGatti(result.data);
                } else {
                    setErrore(result.message);
                }
                setLoading(false);
            })
            .catch(error => {
                setErrore("Errore di connessione al server.");
                setLoading(false);
            });
    }, []);

    // NUOVO EFFETTO: L'Emettitore del Custom Event per il Vanilla JS
    // Questo scatta ogni volta che l'array "gattiSelezionati" cambia.
    useEffect(() => {
        const evento = new CustomEvent('aggiornamentoGattiScelti', {
            detail: gattiSelezionati // Il "pacchetto" di dati che spediamo
        });
        document.dispatchEvent(evento); // Lanciamo l'evento a livello di documento globale
    }, [gattiSelezionati]);

    // NUOVA FUNZIONE: Gestione del click sulla card
    const gestisciSelezione = (gatto) => {
        // Se la costante globale IS_LOGGED_IN (dal PHP) è falsa, blocca il click
        if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
            return;
        }

        setGattiSelezionati(prevSelezionati => {
            const giaPresente = prevSelezionati.find(g => g.id === gatto.id);
            if (giaPresente) {
                // Se era già selezionato, lo rimuoviamo dall'array
                return prevSelezionati.filter(g => g.id !== gatto.id);
            } else {
                // Altrimenti, lo aggiungiamo
                return [...prevSelezionati, gatto];
            }
        });
    };

    const gattiVisualizzati = gatti.filter(gatto => {
        const testo = ricercaTesto.toLowerCase();
        const nome = gatto.nome ? gatto.nome.toLowerCase() : "";
        const desc = gatto.descrizione ? gatto.descrizione.toLowerCase() : "";
        return nome.includes(testo) || desc.includes(testo);
    });

    gattiVisualizzati.sort((a, b) => {
        if (criterioOrdinamento === 'eta_asc') return a.eta - b.eta;
        if (criterioOrdinamento === 'eta_desc') return b.eta - a.eta;
        if (criterioOrdinamento === 'data_desc') return new Date(b.data_arrivo) - new Date(a.data_arrivo);
        if (criterioOrdinamento === 'colore_asc') return (a.colore_mantello || "").localeCompare(b.colore_mantello || "");
        return 0;
    });

    if (loading) return <div className="messaggio-di-stato">Caricamento ospiti in corso...</div>;
    if (errore) return <div className="messaggio-errore">{errore}</div>;

    return (
        <div className="react-container">
            <div className="filtri-ricerca">
                <input 
                    type="text" 
                    placeholder="Cerca per nome o descrizione..." 
                    value={ricercaTesto}
                    onChange={(e) => setRicercaTesto(e.target.value)}
                    className="input-ricerca"
                />
                <select 
                    value={criterioOrdinamento}
                    onChange={(e) => setCriterioOrdinamento(e.target.value)}
                    className="select-ordinamento"
                >
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
                        // Verifichiamo se questo specifico gatto è nell'array dei selezionati
                        const isSelezionato = gattiSelezionati.some(g => g.id === gatto.id);
                        // Determiniamo le classi dinamiche
                        let classiCard = "card-gatto-premium";
                        if (typeof IS_LOGGED_IN !== 'undefined' && IS_LOGGED_IN) classiCard += " card-cliccabile";
                        if (isSelezionato) classiCard += " selezionata";

                        return (
                            <div 
                                key={gatto.id} 
                                className={classiCard}
                                onClick={() => gestisciSelezione(gatto)}
                            >
                                <div className="card-img-wrapper">
                                    <span className="badge-nuovo">
                                        {gatto.sesso === 'M' ? 'Maschio' : 'Femmina'}
                                    </span>
                                    <img 
                                        src={gatto.foto_url ? gatto.foto_url : 'assets/img/placeholder_gatto.png'} 
                                        alt={"Foto di " + gatto.nome} 
                                    />
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
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}

const root = ReactDOM.createRoot(document.getElementById('react-root'));
root.render(<GattiApp />);
export default function HelpSearch({ value, onChange }) {
    return (
        <div className="akd-help-search">
            <i className="fa-solid fa-magnifying-glass akd-help-search__icon" aria-hidden="true" />
            <input
                type="search"
                className="akd-help-search__input"
                placeholder="Search for articles..."
                value={value}
                onChange={(event) => onChange(event.target.value)}
                aria-label="Search the Help Centre"
            />
        </div>
    );
}
const RuleIconSvg = ( { children } ) => (
	<svg
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		strokeWidth="1.7"
		strokeLinecap="round"
		strokeLinejoin="round"
		role="presentation"
		aria-hidden="true"
	>
		{ children }
	</svg>
);

const RuleIcon = ( { name } ) => {
	switch ( name ) {
		case 'user_role':
			return (
				<RuleIconSvg>
					<circle cx="12" cy="9" r="4" />
					<path d="M5 20c0-4 3-7 7-7s7 3 7 7" />
				</RuleIconSvg>
			);
		case 'device':
			return (
				<RuleIconSvg>
					<rect x="8" y="3" width="8" height="18" rx="1.5" />
					<circle cx="12" cy="18" r="0.8" />
				</RuleIconSvg>
			);
		case 'login_status':
			return (
				<RuleIconSvg>
					<rect x="6" y="10" width="12" height="9" rx="1" />
					<path d="M9 10V7a3 3 0 0 1 6 0v3" />
				</RuleIconSvg>
			);
		case 'signup_date':
		case 'within_date_range':
			return (
				<RuleIconSvg>
					<rect x="4" y="6" width="16" height="14" rx="2" />
					<path d="M8 3v3M16 3v3M4 10h16M9 14h2M13 14h2M9 17h2M13 17h2" />
				</RuleIconSvg>
			);
		case 'browser_language':
			return (
				<RuleIconSvg>
					<circle cx="12" cy="12" r="9" />
					<path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" />
				</RuleIconSvg>
			);
		case 'days_of_week':
			return (
				<RuleIconSvg>
					<rect x="3" y="5" width="18" height="16" rx="2" />
					<path d="M3 9h18M7 3v2M17 3v2M7 13h2M11 13h2M15 13h2M7 17h2M11 17h2M15 17h2" />
				</RuleIconSvg>
			);
		case 'within_time':
			return (
				<RuleIconSvg>
					<circle cx="12" cy="12" r="9" />
					<path d="M12 7v6l3 2" />
				</RuleIconSvg>
			);
		case 'url_query_key':
			return (
				<RuleIconSvg>
					<path d="M10.5 6.5 6 11l4.5 4.5" />
					<path d="M13.5 6.5 18 11l-4.5 4.5" />
				</RuleIconSvg>
			);
		case 'utm_campaign':
			return (
				<RuleIconSvg>
					<path d="M4 14V6l12-3v18l-12-3" />
					<path d="M4 10h12" />
				</RuleIconSvg>
			);
		case 'utm_content':
			return (
				<RuleIconSvg>
					<rect x="4" y="4" width="16" height="16" rx="2" />
					<path d="M8 8h8M8 12h8M8 16h5" />
				</RuleIconSvg>
			);
		case 'utm_medium':
			return (
				<RuleIconSvg>
					<path d="M4 13c1.5-2 3.5-3 6-3s4.5 1 6 3" />
					<path d="M4 17c1.5-2 3.5-3 6-3s4.5 1 6 3" />
					<path d="M4 9c1.5-2 3.5-3 6-3s4.5 1 6 3" />
				</RuleIconSvg>
			);
		case 'utm_source':
			return (
				<RuleIconSvg>
					<path d="M12 4v16" />
					<path d="m7 9 5-5 5 5" />
				</RuleIconSvg>
			);
		case 'utm_term':
			return (
				<RuleIconSvg>
					<circle cx="11" cy="11" r="5" />
					<path d="m16 16 3 3" />
				</RuleIconSvg>
			);
		default:
			return (
				<RuleIconSvg>
					<circle cx="12" cy="12" r="9" />
				</RuleIconSvg>
			);
	}
};

export default RuleIcon;

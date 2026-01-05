import {
    FileDescriptionIcon,
    DatabaseIcon,
    PlayerPlayIcon
} from 'vue-tabler-icons';

export interface menu {
    header?: string;
    title?: string;
    icon?: any;
    to?: string;
    chip?: string;
    chipColor?: string;
    chipBgColor?: string;
    chipVariant?: string;
    chipIcon?: string;
    children?: menu[];
    disabled?: boolean;
    type?: string;
    subCaption?: string;
    external?: boolean;
}

const sidebarItem: menu[] = [
    { header: 'Reports' },
    {
        title: 'Reports',
        icon: FileDescriptionIcon,
        to: '/reports',
    },
    {
        title: 'Data Sources',
        icon: DatabaseIcon,
        to: '/data-sources',
    },
    {
        title: 'Execute Report',
        icon: PlayerPlayIcon,
        to: '/execute-report',
    },
];

export default sidebarItem;

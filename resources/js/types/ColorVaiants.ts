export type ClassVariant =
    | 'Death Knight'
    | 'Demon Hunter'
    | 'Druid'
    | 'Evoker'
    | 'Hunter'
    | 'Mage'
    | 'Monk'
    | 'Paladin'
    | 'Priest'
    | 'Rogue'
    | 'Shaman'
    | 'Warlock'
    | 'Warrior';

export type ColorVariants = Record<ClassVariant, string> & { [key: string]: string };

export const colorVariants: ColorVariants = {
    'Death Knight': 'text-death-knight',
    'Demon Hunter': 'text-demon-hunter',
    'Druid': 'text-druid',
    'Evoker': 'text-evoker',
    'Hunter': 'text-hunter',
    'Mage': 'text-mage',
    'Monk': 'text-monk',
    'Paladin': 'text-paladin',
    'Priest': 'text-priest',
    'Rogue': 'text-rogue',
    'Shaman': 'text-shaman',
    'Warlock': 'text-warlock',
    'Warrior': 'text-warrior',
}

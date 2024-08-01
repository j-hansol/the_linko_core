<x-guest-layout>
    <x-slot:title>Visa OCR Registration</x-slot:title>
    <x-markdown class="markdown-body">
# 1차 시도
## 데이터 셋트
아래 에이터를 암호화하여 전송
```json
{
    "country-of-birth": "ARGENTINE REPUBLIC",
    "date-of-birth": "1970-12-24",
    "family-name": "zappier",
    "given-names": "Pier",
    "national-identity-no": "PC324334090",
    "nationality": "ARGENTINE REPUBLIC",
    "other-citizen-country-name": null,
    "other-family-name-used-enter-kore": null,
    "other-given-name-used-enter-korea": null,
    "status-of-stay": "Working",
    "address-of-school": "Test School",
    "country-of-passport": "ARGENTINE REPUBLIC",
    "current-residential-address": "Test Current address",
    "date-of-expire": "2024-01-15",
    "date-of-issue": "2029-01-14",
    "email": "test@soho.s",
    "emergency-contact-country-residence": "ARGENTINE REPUBLIC",
    "emergency-contact-full-name": "Test Emergency Name",
    "emergency-contact-relationship": "Test R",
    "emergency-contact-telephone-no": "12345",
    "home-country-address": "Home Address",
    "name-of-school": "Test School",
    "number-of-children": 0,
    "passport-no": "P123456",
    "place-of-issue": "CAPIZ",
    "spouse-contact-no": "1234567",
    "spouse-date-of-birth": "1974-01-15",
    "spouse-family-name": "S",
    "spouse-given-name": "G",
    "spouse-nationality": "ARGENTINE REPUBLIC",
    "spouse-residential-address": "Test S R Address",
    "telephone-no": "12345",
    "address-in-korea": "Test korea Address",
    "address-of-company": "Test Company Address",
    "contact-no-in-korea": "123456",
    "intended-date-of-entr": "2024-02-15",
    "intended-period-of-stay": 180,
    "name-of-company": "Test Company",
    "position": "Test Position",
    "telephone-no2": "12345678",
    "address": "Test Address",
    "assistance-date-of-birth": "1968-01-15",
    "assistance-full-name": "Test Assistance",
    "assistance-relationship": "R",
    "assistance-telephone-no": "123456",
    "date-of-birth-and-business-registration-no": "1968-01-15/1234-12345-1234",
    "estimated-travel-cost-usd": 0,
    "funding-contact-no": null,
    "funding-name-of-person-organization": null,
    "funding-relationship-to-applicant": null,
    "funding-type-of-support": null,
    "inviting-applicant-relationship": null,
    "name-of-inviting-person-and-organization": "Test Invitor",
    "phone-no-inviting-applicant": "+825112341234/+821023412345"
}
```

# 수정된 데이터 셋트(무딘으로부터 받은 구조)
```
interface VisaApplication {
    family_name: string; // 1.1
    given_names: string; // 1.1
    hanja_name: string; // 1.2
    sex: string; // 1.3  M, F
    birthday: Date; // 1.4
    nationality: string; // 1.5
    birth_country: string; // 1.6
    identity_no: string; // 1.7
    has_other_names: boolean; // 1.8
    other_family_name: string; // 1.8 if Yes
    other_given_name: string; // 1.8 if Yes
    has_other_citizen_countries: boolean; // 1.9
    other_citizen_countries: string[]; // 1.9 if Yes
    stay_period: string; // 2.1
    stay_status: string; // 2.2
    passport: {
        passport_type: number; // 3.1  10, 20, 30, 990
        other_type_detail: string; // 3.1 if Other type (990)
        passport_no: string; // 3.2
        passport_country: string; // 3.3
        issue_place: string; // 3.4
        issue_date: Date; // 3.5
        expire_date: Date; // 3.6
    };
    has_other_passport: boolean; // 3.7
    other_passport: {
        // If 3.7 is Yes
        passport_type: number; // 3.7-a   10, 20, 30, 990
        other_type_detail: string; // 3.7-a if Other type (990)
        passport_no: string; // 3.7-b
        passport_country: string; // 3.7-c
        expire_date: Date; // 3.7-d
    };
    contact: { // 4
        home_address: string; // 4.1
        current_address: string; // 4.2
        cell_phone: string; // 4.3
        email: string; // 4.4
        emergency_full_name: string; // 4.5-a
        emergency_country: string; // 4.5-b
        emergency_telephone: string; // 4.5-c
        emergency_relationship: string; // 4.5-d
    };
    family: { // 5
        marital_status: number; // 5.1   10, 20, 30
        spouse: {
            // 5.2 if Married (10)
            family_name: string; // 5.2-a
            given_names: string; // 5.2-b
            birthday: string; // 5.2-c
            nationality: string; // 5.2-d
            residential_address: string; // 5.2-e
            contact_no: string; // 5.2-f
        };
        has_children: boolean; // 5.3
        number_of_children: number; // 5.3 if Yes
    };
    education: { // 6
        highest_degree: number; // 6.1   30, 20, 10, 990
        other_detail: string; //  6.1 if other (990)
        school_name: string; // 6.2
        school_location: string; // 6.3
    };
    employment: { // 7
        job: number; // 7.1 10,20,...70, 990
        other_detail: string; // if other (990)
        org_name: string; // 7.2-a
        position_course: string; // 7.2-b
        org_address: string; // 7.2-c
        org_telephone: string; // 7.2-d
    };
    visit_detail: {
        purpose: number; // 8.1
        other_purpose_detail: string; // if 8.1 Other
        intended_stay_period: number; // 8.2
        intended_entry_date: string; // 8.3
        address_in_korea: string; // 8.4
        contact_in_korea: string; // 8.5
        has_visit_korea: boolean; // 8.6
        visit_list: [ // if 8.6 Yes
            {
                visit_purpose: string,
                period_of_stay: string
            }
        ];
        has_visit_countries: boolean; // 8.7
        visit_countries: [ // if 8.7 Yes
            {
                country_name: string;
                visit_purpose: string;
                period_of_stay: string; // date1 ~ date2
            }
        ];
        has_family_members_in_korea: boolean; // 8.8
        family_members_in_korea: [  // if 8.8 Yes
            {
                full_name: string;
                birthday: string;
                nationality: string;
                relationship: string;
            }
        ];
        has_family_members_traveling: boolean; // 8.9
        family_members_traveling: [ // if 8.9 Yes
            {
                full_name: string;
                birthday: string;
                nationality: string;
                relationship: string;
            }
        ]
    };
    has_invitor: boolean; // 9.1
    invitor: {
        name: string, // 9.1-a
        birthday_or_registration_no: string, // 9.1-b
        relationship: string, // 9.1-c
        address: string, // 9.1-d
        phone_no: string // 9.1-e
    },
    funding_detail: { // 10
        travel_costs: number, // 10.1
        name: string, // 10.2-a
        relationship: string, // 10.2-b
        support_type: string, // 10.2-c
        contact_no: string, // 10.2-d
    },
    has_assistant: boolean, // 11.1
    assistant: {  // if 11.1 is Yes
        full_name: string,
        birthday: string,
        phone_no: string,
        relationship: string
    }
}
```

## 테스트 데이터 1
```json
{
    "family_name": "zappier",
    "given_names": "Muzaffar",
    "hanja_name": "",
    "sex": "M",
    "birthday": "2024-01-17",
    "nationality": "ANTARCTICA",
    "birth_country": "ANTARCTICA",
    "identity_no": "1234567",
    "has_other_names": false,
    "other_family_name": "",
    "other_given_name": "",
    "has_other_citizen_countries": false,
    "other_citizen_countries": [],
    "stay_period": 10,
    "stay_status": "Test",
    "passport": {
        "passport_type": 10,
        "other_type_detail": "",
        "passport_no": "F2345678",
        "passport_country": "ANTARCTICA",
        "issue_place": "Test",
        "issue_date": "2024-01-17",
        "expire_date": "2029-01-17"
    },
    "has_other_passport": false,
        "other_passport": {
        "passport_type": 0,
        "other_type_detail": "",
        "passport_no": "",
        "passport_country": "",
        "expire_date": ""
    },
    "contact": {
        "home_address": "Test Home Address",
        "current_address": "Test Current Address",
        "cell_phone": "1234567",
        "email": "f@fake.com",
        "emergency_full_name": "Test E Name",
        "emergency_country": "ANTARCTICA",
        "emergency_telephone": "234567",
        "emergency_relationship": "MOL"
    },
    "family": {
        "marital_status": 10,
        "spouse": {
            "family_name": "A",
            "given_names": "B",
            "birthday": "1970-01-17",
            "nationality": "ANTARCTICA",
            "residential_address": "Test S Address",
            "contact_no": "1234567"
        },
        "has_children": true,
        "number_of_children": 1
    },
    "education": {
        "highest_degree": 10,
        "other_detail": "",
        "school_name": "wedfgh",
        "school_location": "Test SC Address"
    },
    "employment": {
        "job": 10,
        "other_detail": "",
        "org_name": "wertyh",
        "position_course": "qwert",
        "org_address": "Test T Address",
        "org_telephone": "23456"
    },
    "visit_detail": {
        "purpose": 10,
        "other_purpose_detail": "",
        "intended_stay_period": 7,
        "intended_entry_date": "2024-01-17",
        "address_in_korea": "Test K Address",
        "contact_in_korea": "123456",
        "has_visit_korea": true,
        "visit_list": [
            {
                "visit_purpose": "입출고",
                "period_of_stay": "2019-01-01 ~ 2019-01-05"
            }
        ],
        "has_visit_countries": true,
        "visit_countries": [
            {
                "country_name": "KOSOVO",
                "visit_purpose": "Test",
                "period_of_stay": "2019-04-01 ~ 2019-04-03"
            }
        ],
        "has_family_members_in_korea": true,
        "family_members_in_korea": [
            {
                "full_name": "AAAA AA",
                "birthday": "2001-01-17",
                "nationality": "ANTARCTICA",
                "relationship": "Son"
            }
        ],
        "has_family_members_traveling": true,
        "family_members_traveling": [
            {
                "full_name": "B A",
                "birthday": "1970-01-17",
                "nationality": "ANTARCTICA",
                "relationship": "Wife"
            }
        ]
    },
    "has_invitor": true,
    "invitor": {
        "name": "Test Name",
        "birthday_or_registration_no": "1970-01-01/122-12-12345234",
        "relationship": "A",
        "address": "Test Address",
        "phone_no": "+82-10-2643-7824/051-2312-1234"
    },
    "funding_detail": {
        "travel_costs": 1000,
        "name": "A",
        "relationship": "MOL",
        "support_type": "TESt",
        "contact_no": "123456"
    },
    "has_assistant": true,
    "assistant": {
        "full_name": "Test A",
        "birthday": "1970-01-17",
        "phone_no": "+82-10-2643-7824",
        "relationship": "TEST R"
    }
}
```

## 여권정보 데이터셋트

```json
{
    'date-of-issue': '23 FEB 2022',
    'given-name': 'RANDY',
    'date-of-birth': '27 JUN 1998',
    'country-code': 'PHL',
    'issuing-authority': 'DFA ILOILO',
    'passport-number': 'P9043042B',
    'nationality': 'FILIPINO',
    'middle-name': 'ANDRADA',
    'sex': 'M',
    'valid-until': '22 FEB 2032',
    'place-of-birth': 'MAAYON CAPIZ',
    'surname': 'BONGANAY'
}
```
    </x-markdown>
</x-guest-layout>
